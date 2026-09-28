-- Read-only MySQL examples for the migrated talentsync database.
-- Open listings with company/category (JOIN and filtering).
SELECT j.id,j.title,u.company,c.name AS category,j.location,j.salary_min,j.salary_max
FROM job_listings j JOIN users u ON u.id=j.employer_id
JOIN categories c ON c.id=j.category_id
WHERE j.status='published' AND j.deadline>=CURRENT_DATE
ORDER BY j.featured DESC,j.created_at DESC LIMIT 9 OFFSET 0;

-- Application counts including jobs with zero applicants.
SELECT j.id,j.title,COUNT(a.id) AS applications
FROM job_listings j LEFT JOIN applications a ON a.job_listing_id=j.id
GROUP BY j.id,j.title ORDER BY applications DESC;

-- One seeker's application pipeline (substitute the actual user ID).
SELECT u.name,j.title,a.status,a.created_at FROM applications a
JOIN users u ON u.id=a.user_id JOIN job_listings j ON j.id=a.job_listing_id
WHERE a.user_id=4;

-- Saved jobs for a seeker.
SELECT j.title,j.deadline,j.status FROM bookmarks b
JOIN job_listings j ON j.id=b.job_listing_id WHERE b.user_id=4;

-- Listings without applicants (subquery).
SELECT j.title FROM job_listings j
WHERE NOT EXISTS (SELECT 1 FROM applications a WHERE a.job_listing_id=j.id);

-- Aggregation by category.
SELECT c.name,COUNT(j.id) AS job_count,AVG(j.salary_min) AS average_minimum_salary
FROM categories c LEFT JOIN job_listings j ON j.category_id=c.id
GROUP BY c.id,c.name;

-- Inspect keys and indexes for the presentation.
SHOW CREATE TABLE applications;
SHOW INDEX FROM job_listings;
