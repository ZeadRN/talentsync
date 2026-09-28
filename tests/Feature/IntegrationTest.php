<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
class IntegrationTest extends TestCase {
 use RefreshDatabase;
 protected function setUp():void{parent::setUp();$this->seed();}
 public function test_database_notification_is_persisted():void{config(['queue.default'=>'sync','mail.default'=>'log','broadcasting.default'=>'log']);$seeker=User::where('email','seeker@talentsync.test')->first();$employer=User::where('email','employer2@talentsync.test')->first();$this->actingAs($seeker)->post('/jobs/2/apply')->assertRedirect();$this->assertEquals(1,$employer->notifications()->count());$this->actingAs($employer)->post('/notifications/read')->assertRedirect();$this->assertEquals(0,$employer->unreadNotifications()->count());}
 public function test_avatar_upload_is_stored():void{Storage::fake('public');$user=User::where('email','seeker@talentsync.test')->first();$png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');$this->actingAs($user)->put('/profile',['name'=>$user->name,'avatar'=>UploadedFile::fake()->createWithContent('avatar.png',$png)])->assertSessionHasNoErrors()->assertRedirect();Storage::disk('public')->assertExists($user->fresh()->avatar);}
}
