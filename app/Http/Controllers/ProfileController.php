<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ProfileController extends Controller {
 public function edit(Request $request){return view('profile',['user'=>$request->user()]);}
 public function update(Request $request){
  $data=$request->validate(['name'=>'required|string|max:255','bio'=>'nullable|string|max:2000','skills'=>'nullable|string|max:1000','location'=>'nullable|string|max:255','company'=>'nullable|string|max:255','avatar'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048','resume'=>'nullable|file|mimes:pdf|max:5120','company_description'=>'nullable|string|max:5000','company_website'=>'nullable|url:http,https|max:255','company_size'=>'nullable|in:1–10,11–50,51–200,201–500,500+']);
  $old=[];$new=[];
  try {
   foreach(['avatar'=>'public','resume'=>'local'] as $field=>$disk){unset($data[$field]);if($request->hasFile($field)){$old[$field]=$request->user()->$field;$data[$field]=$request->file($field)->store($field==='avatar'?'avatars':'resumes',$disk);$new[$field]=$data[$field];}}
   $request->user()->update($data);
  }catch(\Throwable $error){foreach($new as $field=>$file)Storage::disk($field==='avatar'?'public':'local')->delete($file);throw $error;}
  foreach($old as $field=>$file)if($file)Storage::disk($field==='avatar'?'public':'local')->delete($file);
  return back()->with('success','Profile updated.');
 }
 public function resume(Request $request,User $user){
  $allowed=$request->user()->id===$user->id || $request->user()->can('manage platform') || ($request->user()->can('create jobs') && $user->applications()->whereHas('jobListing',fn($jobs)=>$jobs->where('employer_id',$request->user()->id))->exists());
  abort_unless($allowed,403);abort_unless($user->resume && Storage::disk('local')->exists($user->resume),404);
  return Storage::disk('local')->download($user->resume,'resume-'.$user->id.'.pdf',['Content-Type'=>'application/pdf']);
 }
 public function removeResume(Request $request){$file=$request->user()->resume;$request->user()->update(['resume'=>null]);if($file)Storage::disk('local')->delete($file);return back()->with('success','Resume removed.');}
 public function company(User $user){abort_unless(filled($user->company)&&$user->listings()->open()->exists(),404);return view('company',['company'=>$user,'jobs'=>$user->listings()->open()->with(['employer','category'])->latest()->paginate(9)]);}
}