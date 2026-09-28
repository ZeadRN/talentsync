<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\User;
class ProfessionalProfileTest extends TestCase {
 use RefreshDatabase;
 protected function setUp():void{parent::setUp();$this->seed();}
 private function person($name){return User::where('email',$name.'@talentsync.test')->firstOrFail();}
 public function test_resume_download_is_private_and_owner_can_remove():void{
  Storage::fake('local');$seeker=$this->person('seeker');Storage::disk('local')->put('resumes/test.pdf','%PDF-1.4 test');$seeker->update(['resume'=>'resumes/test.pdf']);
  $url='/profiles/'.$seeker->id.'/resume';$this->get($url)->assertRedirect('/login');
  $this->actingAs($this->person('employer2'))->get($url)->assertForbidden();
  $this->actingAs($this->person('employer'))->get($url)->assertOk();
  $this->actingAs($seeker)->get($url)->assertDownload('resume-'.$seeker->id.'.pdf');
  $this->delete('/profile/resume')->assertRedirect();Storage::disk('local')->assertMissing('resumes/test.pdf');$this->assertNull($seeker->fresh()->resume);
 }
 public function test_resume_upload_and_invalid_file():void{
  Storage::fake('local');$user=$this->person('seeker');$this->actingAs($user)->put('/profile',['name'=>$user->name,'resume'=>UploadedFile::fake()->create('cv.pdf',50,'application/pdf')])->assertSessionHasNoErrors();Storage::disk('local')->assertExists($user->fresh()->resume);
  $this->put('/profile',['name'=>$user->name,'resume'=>UploadedFile::fake()->create('cv.php',1,'text/plain')])->assertSessionHasErrors('resume');
 }
 public function test_company_profile_and_safe_website():void{
  $employer=$this->person('employer');$this->get('/companies/'.$employer->id)->assertOk()->assertSee('Northstar Studio');
  $this->actingAs($employer)->put('/profile',['name'=>$employer->name,'company_website'=>'javascript:alert(1)'])->assertSessionHasErrors('company_website');
  $this->put('/profile',['name'=>$employer->name,'company_description'=>'A growing product team.','company_website'=>'https://example.com','company_size'=>'11–50'])->assertSessionHasNoErrors();
  $this->get('/companies/'.$employer->id)->assertSee('A growing product team.');
 }
 public function test_admin_filters_are_authorized_and_work():void{
  $this->actingAs($this->person('seeker'))->get('/admin?q=Samira')->assertForbidden();
  $this->actingAs($this->person('admin'))->get('/admin?q=Samira&role=Employer&job_status=published')->assertOk()->assertViewHas('users',fn($users)=>$users->count()===1 && $users->first()->name==='Samira Ahmed');
 }
}
