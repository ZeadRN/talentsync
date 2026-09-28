<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::table('users',function(Blueprint $table){$table->string('resume')->nullable();$table->text('company_description')->nullable();$table->string('company_website')->nullable();$table->string('company_size')->nullable();});}
 public function down():void{Schema::table('users',fn(Blueprint $table)=>$table->dropColumn(['resume','company_description','company_website','company_size']));}
};