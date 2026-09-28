<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Models\{User,Application,JobListing};
class WorkspaceTest extends TestCase {
 use RefreshDatabase;
 protected function setUp():void{parent::setUp();$this->seed();Notification::fake();}
 private function user($name){return User::where('email',$name.'@talentsync.test')->firstOrFail();}
 public function test_application_history_records_only_changes():void{
  $this->actingAs($this->user('seeker'))->post('/jobs/2/apply')->assertRedirect();
  $application=Application::where('job_listing_id',2)->firstOrFail();
  $this->assertEquals(['applied'],$application->events->pluck('status')->all());
  $this->actingAs($this->user('employer'))->patch('/applications/'.$application->id,['status'=>'hired'])->assertForbidden();
  $this->actingAs($this->user('employer2'))->patch('/applications/'.$application->id,['status'=>'shortlisted'])->assertRedirect();
  $this->patch('/applications/'.$application->id,['status'=>'shortlisted'])->assertRedirect();
  $this->assertEquals(['applied','shortlisted'],$application->fresh()->events->pluck('status')->all());
 }
 public function test_workspace_tabs_and_filters():void{
  $this->actingAs($this->user('seeker'));
  foreach(['overview','applications','saved','notifications'] as $view)$this->get('/dashboard?view='.$view)->assertOk();
  $this->get('/dashboard?view=applications&status=hired')->assertOk()->assertSee('No applications at this stage.');
  $this->actingAs($this->user('employer'))->get('/jobs/1/applications?q=Alex&status=reviewing')->assertOk()->assertSee('Alex Morgan');
  $this->get('/jobs/1/applications?q=nonexistent')->assertOk()->assertSee('No applicants match these filters.');
 }
 public function test_suggestions_exclude_already_applied_and_closed_jobs():void{
  $this->actingAs($this->user('seeker'))->get('/dashboard')->assertOk()->assertViewHas('recommended',fn($jobs)=>!$jobs->contains('id',1)&&$jobs->every(fn($job)=>$job->status==='published'));
 }
 public function test_salary_sort_and_company_search():void{
  $this->get('/?sort=salary')->assertOk()->assertViewHas('jobs',fn($jobs)=>$jobs->first()->salary_max===70000);
  $this->get('/?q=Northstar')->assertOk()->assertViewHas('jobs',fn($jobs)=>$jobs->every(fn($job)=>$job->employer->company==='Northstar Studio'));
 }
}
