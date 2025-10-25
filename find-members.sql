-- Quick diagnostic queries to find members

-- 1. Check if marriages table exists
SHOW TABLES LIKE 'wp_family_marriages';

-- 2. Check if family_members table exists
SHOW TABLES LIKE 'wp_family_members';

-- 3. List ALL members (to see what IDs exist)
SELECT id, first_name, middle_name, last_name, gender, birth_date
FROM wp_family_members
ORDER BY id ASC
LIMIT 20;

-- 4. Count total members
SELECT COUNT(*) as total_members FROM wp_family_members;

-- 5. Check if John Smith was created (from our test data)
SELECT id, first_name, middle_name, last_name
FROM wp_family_members
WHERE first_name = 'John' AND last_name = 'Smith';

-- 6. List all marriages
SELECT * FROM wp_family_marriages;

-- 7. Check if there are any members with the old marriage_date field
SELECT id, first_name, last_name, marriage_date
FROM wp_family_members
WHERE marriage_date IS NOT NULL
LIMIT 10;
