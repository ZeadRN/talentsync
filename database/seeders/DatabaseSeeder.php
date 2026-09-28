<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Category;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['create jobs', 'apply jobs', 'manage platform', 'assign roles'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        foreach (['Super Admin' => [], 'Admin' => ['manage platform', 'create jobs'], 'Employer' => ['create jobs'], 'Job Seeker' => ['apply jobs']] as $role => $permissions) {
            Role::findOrCreate($role, 'web')->syncPermissions($permissions);
        }
        $accounts = [['super', 'Super Admin', 'Platform Owner', null], ['admin', 'Admin', 'Platform Admin', null], ['employer', 'Employer', 'Samira Ahmed', 'Northstar Studio'], ['seeker', 'Job Seeker', 'Alex Morgan', null], ['employer2', 'Employer', 'Rafi Hasan', 'Orbit Labs']];
        $users = [];
        foreach ($accounts as [$email,$role,$name,$company]) {
            $user = User::firstOrCreate(['email' => $email.'@talentsync.test'], ['name' => $name, 'password' => 'TalentSync123!', 'company' => $company, 'location' => 'Dhaka', 'skills' => 'Laravel, SQL, JavaScript', 'bio' => 'Curious, collaborative, and ready to build useful things.']);
            $user->syncRoles([$role]);
            $users[$email] = $user;
        }
        foreach (['Engineering', 'Design', 'Marketing', 'Data & Analytics', 'Business', 'Customer Support'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
        $roles = [['Junior Laravel Developer', 'Engineering', 'Dhaka', 35000, 55000], ['UI / UX Design Intern', 'Design', 'Remote', 15000, 22000], ['Digital Marketing Executive', 'Marketing', 'Dhaka', 28000, 42000], ['Junior Data Analyst', 'Data & Analytics', 'Chattogram', 32000, 50000], ['Frontend Developer', 'Engineering', 'Remote', 40000, 65000], ['Customer Success Associate', 'Customer Support', 'Sylhet', 25000, 35000], ['Business Development Intern', 'Business', 'Dhaka', 12000, 18000], ['Product Designer', 'Design', 'Remote', 45000, 70000], ['QA Engineer', 'Engineering', 'Dhaka', 30000, 45000], ['Content Strategist', 'Marketing', 'Remote', 30000, 48000], ['Database Assistant', 'Data & Analytics', 'Dhaka', 25000, 38000], ['Operations Associate', 'Business', 'Khulna', 27000, 40000]];
        foreach ($roles as $i => [$title,$category,$location,$min,$max]) {
            $job = JobListing::firstOrCreate(['title' => $title], ['employer_id' => $users[$i % 2 ? 'employer2' : 'employer']->id, 'category_id' => Category::where('name', $category)->value('id'), 'location' => $location, 'type' => str_contains($title, 'Intern') ? 'Internship' : 'Full-time', 'salary_min' => $min, 'salary_max' => $max, 'description' => '<h2>Build your next chapter with us</h2><p>We are looking for a motivated '.$title.' to join our friendly, collaborative team. This is an opportunity to learn, contribute, and see your ideas become real.</p><h3>What you will do</h3><ul><li>Collaborate with teammates on meaningful projects.</li><li>Bring fresh ideas and a willingness to learn.</li><li>Document your work and share feedback.</li></ul><h3>What you bring</h3><p>Relevant foundational skills, clear communication, and curiosity. University students and early-career applicants are welcome.</p><h3>Why join us</h3><p>Mentorship, practical experience, and a supportive team that invests in your growth.</p>', 'deadline' => today()->addDays(30 + $i), 'status' => 'published', 'featured' => $i < 3]);
            if ($i === 0) {
                Application::firstOrCreate(['user_id' => $users['seeker']->id, 'job_listing_id' => $job->id], ['status' => 'reviewing']);
            }if ($i === 1) {
                $users['seeker']->bookmarks()->syncWithoutDetaching([$job->id]);
            }
        }
    }
}
