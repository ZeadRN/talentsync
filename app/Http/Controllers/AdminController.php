<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage platform');

        $request->validate(['q'=>'nullable|string|max:100','role'=>'nullable|in:Admin,Super Admin,Employer,Job Seeker','job_status'=>'nullable|in:published,draft,closed']);
        return view('admin', ['users' => User::with('roles')->when($request->q,fn($q,$term)=>$q->where(fn($q)=>$q->where('name','like','%'.$term.'%')->orWhere('email','like','%'.$term.'%')))->when($request->role,fn($q,$role)=>$q->role($role))->paginate(12, ['*'], 'users_page')->withQueryString(), 'jobs' => JobListing::with('employer')->when($request->job_status,fn($q,$status)=>$q->where('status',$status))->latest()->paginate(12, ['*'], 'jobs_page')->withQueryString(), 'applications' => Application::with(['user', 'jobListing'])->latest()->paginate(12, ['*'], 'applications_page'), 'counts' => [User::count(), JobListing::count(), Application::count()]]);
    }

    public function update(Request $r, User $user)
    {
        Gate::authorize('manage platform');
        abort_if($user->hasRole('Super Admin') && ! $r->user()->hasRole('Super Admin'), 403);
        $data = $r->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email,'.$user->id]);
        $user->update($data);

        return back()->with('success', 'User updated.');
    }

    public function role(Request $r, User $user)
    {
        Gate::authorize('assign roles');
        $data = $r->validate(['role' => 'required|in:Super Admin,Admin,Employer,Job Seeker']);
        abort_if($user->id === $r->user()->id, 422, 'Keep your own Super Admin access.');
        $user->syncRoles([$data['role']]);

        return back()->with('success', 'Role updated.');
    }

    public function destroy(Request $r, User $user)
    {
        Gate::authorize('manage platform');
        abort_if($user->id === $r->user()->id || ($user->hasRole('Super Admin') && ! $r->user()->hasRole('Super Admin')), 403);
        $user->delete();

        return back()->with('success', 'User deleted.');
    }

    public function feature(JobListing $job)
    {
        Gate::authorize('manage platform');
        $job->update(['featured' => ! $job->featured]);

        return back()->with('success','Featured status updated.');
    }
}
