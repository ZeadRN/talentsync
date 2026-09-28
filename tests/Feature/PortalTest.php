<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use App\Notifications\PortalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function account($name)
    {
        return User::where('email', $name.'@talentsync.test')->firstOrFail();
    }

    private function payload(): array
    {
        return ['title' => 'Test opportunity', 'location' => 'Remote', 'category_id' => 1, 'type' => 'Full-time', 'salary_min' => 10000, 'salary_max' => 20000, 'deadline' => today()->addDays(10)->toDateString(), 'status' => 'published', 'description' => '<h2>Hello</h2><script>alert(1)</script><p>Safe text</p>'];
    }

    public function test_public_pages_and_filters(): void
    {
        $this->get('/')->assertOk()->assertSee('Good work.');
        $this->get('/?q=Laravel&location=Dhaka&category=1&type=Full-time&salary=50000')->assertOk()->assertSee('Junior Laravel Developer')->assertDontSee('Product Designer');
        $this->get('/?q=nonexistent')->assertSee('No jobs found');
        $this->get('/?page=2')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_registration_cannot_escalate_roles(): void
    {
        $this->post('/register', ['name' => 'Attacker', 'email' => 'a@test.com', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'role' => 'Super Admin'])->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'a@test.com']);
    }

    public function test_registration_login_logout(): void
    {
        $this->post('/register', ['name' => 'Student', 'email' => 'student@test.com', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'role' => 'Job Seeker'])->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertTrue(User::where('email', 'student@test.com')->first()->hasRole('Job Seeker'));
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->post('/login', ['email' => 'student@test.com', 'password' => 'bad'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => 'student@test.com', 'password' => 'Password123!'])->assertRedirect('/dashboard');
    }

    public function test_employer_crud_and_html_sanitization(): void
    {
        $this->actingAs($this->account('employer'))->get('/jobs/create')->assertOk();
        $this->post('/jobs', $this->payload())->assertRedirect();
        $job = JobListing::where('title', 'Test opportunity')->firstOrFail();
        $this->assertStringNotContainsString('<script>', $job->description);
        $this->get('/jobs/'.$job->id.'/edit')->assertOk();
        $this->put('/jobs/'.$job->id, array_merge($this->payload(), ['title' => 'Updated']))->assertRedirect('/dashboard');
        $this->assertDatabaseHas('job_listings', ['id' => $job->id, 'title' => 'Updated']);
        $this->delete('/jobs/'.$job->id)->assertRedirect();
        $this->assertDatabaseMissing('job_listings', ['id' => $job->id]);
    }

    public function test_other_employer_and_seeker_are_forbidden(): void
    {
        $job = JobListing::where('employer_id', $this->account('employer')->id)->first();
        $this->actingAs($this->account('employer2'))->get('/jobs/'.$job->id.'/edit')->assertForbidden();
        $this->put('/jobs/'.$job->id, $this->payload())->assertForbidden();
        $this->delete('/jobs/'.$job->id)->assertForbidden();
        $this->get('/jobs/'.$job->id.'/applications')->assertForbidden();
        $this->actingAs($this->account('seeker'))->post('/jobs', $this->payload())->assertForbidden();
        $this->get('/admin')->assertForbidden();
    }

    public function test_apply_is_unique_and_notifies_once(): void
    {
        Notification::fake();
        $job = JobListing::findOrFail(2);
        $this->actingAs($this->account('seeker'));
        $this->post('/jobs/2/apply')->assertRedirect();
        $this->post('/jobs/2/apply')->assertRedirect();
        $this->assertEquals(1, Application::where('job_listing_id', 2)->where('user_id', auth()->id())->count());
        Notification::assertSentToTimes($job->employer, PortalNotification::class, 1);
        $job->update(['deadline' => today()->subDay()]);
        $this->post('/jobs/2/apply')->assertStatus(422);
    }

    public function test_closed_jobs_hidden_and_application_restricted(): void
    {
        $job = JobListing::findOrFail(2);
        $job->update(['status' => 'draft']);
        $this->get('/jobs/2')->assertNotFound();
        $this->actingAs($this->account('seeker'))->post('/jobs/2/apply')->assertStatus(422);
        $this->actingAs($this->account('employer'))->post('/jobs/1/apply')->assertForbidden();
        $this->actingAs($this->account('super'))->get('/jobs/2')->assertOk();
    }

    public function test_bookmark_add_is_idempotent_and_removable(): void
    {
        $user = $this->account('seeker');
        $this->actingAs($user)->post('/jobs/3/bookmark')->assertRedirect();
        $this->post('/jobs/3/bookmark')->assertRedirect();
        $this->assertEquals(1, $user->bookmarks()->where('job_listings.id', 3)->count());
        $this->delete('/jobs/3/bookmark')->assertRedirect();
        $this->assertEquals(0, $user->bookmarks()->where('job_listings.id', 3)->count());
    }

    public function test_application_status_authorization_and_notification(): void
    {
        Notification::fake();
        $application = Application::first();
        $this->actingAs($this->account('employer2'))->patch('/applications/'.$application->id, ['status' => 'hired'])->assertForbidden();
        $this->actingAs($this->account('employer'))->patch('/applications/'.$application->id, ['status' => 'shortlisted'])->assertRedirect();
        $this->assertDatabaseHas('applications', ['id' => $application->id, 'status' => 'shortlisted']);
        Notification::assertSentTo($application->user, PortalNotification::class);
        $this->patch('/applications/'.$application->id, ['status' => 'invalid'])->assertSessionHasErrors('status');
    }

    public function test_all_role_dashboards_render(): void
    {
        foreach (['seeker', 'employer', 'admin', 'super'] as $role) {
            $this->actingAs($this->account($role))->get('/dashboard')->assertOk();
            $this->get('/profile')->assertOk();
        }foreach (['admin', 'super'] as $role) {
            $this->actingAs($this->account($role))->get('/admin')->assertOk();
        }$this->actingAs($this->account('employer'))->get('/jobs/1/applications')->assertOk();
    }

    public function test_admin_and_super_admin_permissions(): void
    {
        $admin = $this->account('admin');
        $super = $this->account('super');
        $seeker = $this->account('seeker');
        $this->actingAs($admin)->patch('/admin/jobs/1/feature')->assertRedirect();
        $this->patch('/admin/users/'.$seeker->id.'/role', ['role' => 'Admin'])->assertForbidden();
        $this->delete('/admin/users/'.$super->id)->assertForbidden();
        $this->actingAs($super)->patch('/admin/users/'.$seeker->id.'/role', ['role' => 'Employer'])->assertRedirect();
        $this->assertTrue($seeker->fresh()->hasRole('Employer'));
        $this->patch('/admin/users/'.$super->id.'/role', ['role' => 'Job Seeker'])->assertStatus(422);
        $this->delete('/admin/users/'.$super->id)->assertForbidden();
    }

    public function test_profile_and_upload_validation(): void
    {
        Storage::fake('public');
        $this->actingAs($this->account('seeker'))->put('/profile', ['name' => 'New Name', 'bio' => 'About me', 'skills' => 'SQL', 'avatar' => UploadedFile::fake()->create('bad.php', 1, 'application/x-php')])->assertSessionHasErrors('avatar');
        $this->put('/profile', ['name' => 'New Name', 'skills' => 'SQL'])->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => auth()->id(), 'name' => 'New Name', 'skills' => 'SQL']);
    }

    public function test_invalid_salary_range_rejected(): void
    {
        $this->actingAs($this->account('employer'))->post('/jobs', array_merge($this->payload(), ['salary_min' => 50000]))->assertSessionHasErrors('salary_max');
    }

    public function test_job_deletion_cascades(): void
    {
        $this->actingAs($this->account('employer'))->delete('/jobs/1')->assertRedirect();
        $this->assertDatabaseMissing('applications',['job_listing_id' => 1]);
    }
}
