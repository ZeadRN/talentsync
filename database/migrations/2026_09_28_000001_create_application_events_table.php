<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void {
  Schema::create('application_events',function(Blueprint $table){
   $table->id();
   $table->foreignId('application_id')->constrained()->cascadeOnDelete();
   $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
   $table->string('status',20);
   $table->timestamps();
  });
  // Preserve existing application progress when upgrading an installed portal.
  DB::table('applications')->orderBy('id')->chunkById(100,function($applications){foreach($applications as $application){DB::table('application_events')->insert(['application_id'=>$application->id,'actor_id'=>null,'status'=>$application->status,'created_at'=>$application->updated_at,'updated_at'=>$application->updated_at]);}});
 }
 public function down(): void {Schema::dropIfExists('application_events');}
};
