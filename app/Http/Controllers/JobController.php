<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q'=>'nullable|string|max:100', 'location'=>'nullable|string|max:100',
            'category'=>'nullable|integer|exists:categories,id',
            'type'=>'nullable|in:Full-time,Part-time,Internship,Contract',
            'salary'=>'nullable|integer|min:0|max:10000000',
            'sort'=>'nullable|in:recommended,newest,salary,deadline',
        ]);
        $query=JobListing::with(['employer','category'])->open()
            ->when($request->q,fn($q,$value)=>$q->where(function($search)use($value){
                $search->where('title','like','%'.$value.'%')
                    ->orWhereHas('employer',fn($employer)=>$employer->where('company','like','%'.$value.'%'));
            }))
            ->when($request->location,fn($q,$value)=>$q->where('location','like','%'.$value.'%'))
            ->when($request->category,fn($q,$value)=>$q->where('category_id',$value))
            ->when($request->type,fn($q,$value)=>$q->where('type',$value))
            ->when($request->salary,fn($q,$value)=>$q->where('salary_max','>=',$value));
        match($request->input('sort','recommended')) {
            'salary'=>$query->orderByDesc('salary_max')->latest(),
            'deadline'=>$query->orderBy('deadline')->latest(),
            'newest'=>$query->latest(),
            default=>$query->orderByDesc('featured')->latest(),
        };
        $jobs=$query->paginate(9)->withQueryString();
        $categories=Category::withCount(['listings'=>fn($q)=>$q->open()])->get();
        $total=JobListing::open()->count();
        $companyCount=JobListing::open()->distinct()->count('employer_id');
        $savedIds=$request->user()?->bookmarks()->pluck('job_listings.id')->all()??[];
        return view('jobs.index',compact('jobs','categories','total','companyCount','savedIds'));
    }

    public function show(JobListing $job)
    {
        abort_unless(($job->status === 'published' && $job->deadline->gte(today())) || (auth()->check() && auth()->user()->can('manage-job', $job)), 404);

        return view('jobs.show', ['job' => $job->load(['employer', 'category'])]);
    }

    public function create()
    {
        Gate::authorize('create jobs');

        return view('jobs.form', ['job' => new JobListing, 'categories' => Category::all()]);
    }

    public function store(Request $r)
    {
        Gate::authorize('create jobs');
        $job = $r->user()->listings()->create($this->validated($r));

        return redirect()->route('jobs.show', $job)->with('success', 'Your job has been created.');
    }

    public function edit(JobListing $job)
    {
        Gate::authorize('manage-job', $job);

        return view('jobs.form', ['job' => $job, 'categories' => Category::all()]);
    }

    public function update(Request $r, JobListing $job)
    {
        Gate::authorize('manage-job', $job);
        $job->update($this->validated($r));

        return redirect('/dashboard')->with('success', 'Job updated.');
    }

    public function destroy(JobListing $job)
    {
        Gate::authorize('manage-job', $job);
        $job->delete();

        return back()->with('success', 'Job deleted.');
    }

    private function validated(Request $r): array
    {
        $d = $r->validate(['title' => 'required|string|max:255', 'category_id' => 'required|exists:categories,id', 'location' => 'required|string|max:255', 'type' => 'required|in:Full-time,Part-time,Internship,Contract', 'salary_min' => 'required|integer|min:0|max:10000000', 'salary_max' => 'required|integer|gte:salary_min|max:10000000', 'description' => 'required|string|max:30000', 'deadline' => 'required|date|after_or_equal:today', 'status' => 'required|in:draft,published,closed']);
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,h2,h3,ul,ol,li,a[href|title],blockquote');
        $config->set('Cache.DefinitionImpl', null);
        $d['description'] = (new \HTMLPurifier($config))->purify($d['description']);

        return $d;
    }
}
