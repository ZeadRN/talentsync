<?php
namespace App\Http\Controllers;
use App\Models\{JobListing,Application};
use Illuminate\Http\Request;
class DashboardController extends Controller {
 public function index(Request $request) {
  $request->validate(['status'=>'nullable|in:applied,reviewing,shortlisted,hired,rejected','view'=>'nullable|in:overview,applications,saved,jobs,notifications']);
  $user=$request->user();
  $hiring=$user->can('create jobs');
  $jobQuery=JobListing::query()->when(!$user->can('manage platform'),fn($query)=>$query->where('employer_id',$user->id));
  $applicationQuery=Application::query()->when($hiring,fn($query)=>$query->whereIn('job_listing_id',(clone $jobQuery)->select('id')),fn($query)=>$query->where('user_id',$user->id));
  $pipeline=(clone $applicationQuery)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total','status');
  $totalApplications=$pipeline->sum();
  $stats=['applications'=>$totalApplications,'shortlisted'=>(int)($pipeline['shortlisted']??0),'hired'=>(int)($pipeline['hired']??0),'saved'=>$user->bookmarks()->count(),'active_jobs'=>(clone $jobQuery)->open()->count(),'all_jobs'=>(clone $jobQuery)->count(),'unread'=>$user->unreadNotifications()->count()];
  $recent=(clone $applicationQuery)->where('created_at','>=',today()->subDays(13))->get(['created_at']);
  $activity=collect(range(13,0))->map(function($days)use($recent){$date=today()->subDays($days);return ['label'=>$date->format('d M'),'count'=>$recent->filter(fn($application)=>$application->created_at->isSameDay($date))->count()];});
  $maxActivity=max(1,$activity->max('count'));
  $profileFields=collect(['name'=>'Your name','bio'=>'A short introduction','skills'=>'Your skills','location'=>'Your location','avatar'=>'A profile photo']);
  $completed=$profileFields->filter(fn($label,$field)=>filled($user->$field))->count();
  $profileProgress=(int)round($completed/$profileFields->count()*100);
  $applications=$user->applications()->with(['jobListing.employer','events'])->when($request->status,fn($query,$status)=>$query->where('status',$status))->latest()->paginate(8,['*'],'applications_page')->withQueryString();
  $jobs=(clone $jobQuery)->with('category')->withCount('applications')->latest()->paginate(8,['*'],'jobs_page')->withQueryString();
  $bookmarks=$user->bookmarks()->with(['employer','category'])->latest('bookmarks.created_at')->get();
  // Transparent rules-based suggestions; no invented AI match percentages.
  $skills=collect(explode(',',mb_strtolower($user->skills??'')))->map(fn($skill)=>trim($skill))->filter(fn($skill)=>mb_strlen($skill)>1);
  $recommended=JobListing::with(['employer','category'])->open()->where('employer_id','!=',$user->id)->whereDoesntHave('applications',fn($query)=>$query->where('user_id',$user->id))->latest()->limit(100)->get()->map(function($job)use($skills,$user){
   $text=mb_strtolower($job->title.' '.strip_tags($job->description));
   $matches=$skills->filter(fn($skill)=>str_contains($text,$skill));
   $location=filled($user->location)&&mb_strtolower($job->location)===mb_strtolower($user->location);
   $job->suggestion_score=$matches->count()*3+($location?1:0);
   $job->suggestion_reason=$matches->isNotEmpty()?'Matches '.ucfirst($matches->first()):($location?'In your location':'Recently added');
   return $job;
  })->sortByDesc('suggestion_score')->take(3);
  $notifications=$user->notifications()->latest()->limit(20)->get();
  $recentApplicants=(clone $applicationQuery)->with(['user','jobListing'])->latest()->limit(5)->get();
  return view('dashboard',compact('user','hiring','pipeline','stats','activity','maxActivity','profileFields','profileProgress','applications','jobs','bookmarks','recommended','notifications','recentApplicants'));
 }
 public function read(Request $request){$request->user()->unreadNotifications->markAsRead();return back()->with('success','Notifications marked as read.');}
}
