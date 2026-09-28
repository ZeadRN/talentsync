<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobListing;
use App\Notifications\PortalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
    public function store(Request $r, JobListing $job)
    {
        Gate::authorize('apply jobs');
        abort_unless($job->status === 'published' && $job->deadline->gte(today()), 422, 'This job is closed.');
        abort_if($job->employer_id === $r->user()->id, 422, 'You cannot apply to your own job.');
        DB::transaction(function () use ($r, $job) {
            $application = Application::firstOrCreate(['user_id' => $r->user()->id, 'job_listing_id' => $job->id]);
            if ($application->wasRecentlyCreated) {
                $application->events()->create(['actor_id' => $r->user()->id, 'status' => 'applied']);
                $job->employer->notify(new PortalNotification($r->user()->name.' applied for '.$job->title, '/jobs/'.$job->id.'/applications'));
            }
        });

        return back()->with('success', 'Application received. Track it from your dashboard.');
    }

    public function index(Request $r, JobListing $job)
    {
        Gate::authorize('manage-job', $job);

        $r->validate(['status'=>'nullable|in:applied,reviewing,shortlisted,hired,rejected','q'=>'nullable|string|max:100']);
        $counts=$job->applications()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total','status');
        $applications=$job->applications()->with(['user','events'])->when($r->status,fn($query,$status)=>$query->where('status',$status))->when($r->q,fn($query,$search)=>$query->whereHas('user',fn($users)=>$users->where('name','like','%'.$search.'%')->orWhere('skills','like','%'.$search.'%')))->latest()->paginate(12)->withQueryString();
        return view('applications',compact('job','applications','counts'));
    }

    public function update(Request $r, Application $application)
    {
        Gate::authorize('manage-job', $application->jobListing);
        $data = $r->validate(['status' => 'required|in:applied,reviewing,shortlisted,hired,rejected']);
        DB::transaction(function () use ($application, $data, $r) {
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            if ($application->status !== $data['status']) {
                $application->update($data);
                $application->events()->create(['actor_id' => $r->user()->id, 'status' => $data['status']]);
                $application->user->notify(new PortalNotification('Your application for '.$application->jobListing->title.' is now '.$data['status'].'.', '/dashboard'));
            }
        });

        return back()->with('success', 'Application status updated.');
    }

    public function destroy(Application $application)
    {
        Gate::authorize('manage platform');
        $application->delete();

        return back()->with('success', 'Application deleted.');
    }

    public function bookmark(Request $r, JobListing $job)
    {
        Gate::authorize('apply jobs');
        abort_unless($job->status === 'published', 404);
        $r->user()->bookmarks()->syncWithoutDetaching([$job->id]);

        return back()->with('success', 'Job saved.');
    }

    public function unsave(Request $r, JobListing $job)
    {
        Gate::authorize('apply jobs');
        $r->user()->bookmarks()->detach($job->id);

        return back()->with('success','Bookmark removed.');
    }
}
