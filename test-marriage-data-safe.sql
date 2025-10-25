-- Safe Test Data: Creates test data with checks
-- Run this AFTER plugin activation

-- Step 1: Check if we have any clans, if not create one
INSERT INTO wp_family_clans (clan_name, description, created_at, updated_at)
SELECT 'Test Clan', 'Test clan for marriages demo', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM wp_family_clans LIMIT 1);

-- Get first available clan ID
SET @clan_id = (SELECT id FROM wp_family_clans ORDER BY id ASC LIMIT 1);

-- Step 2: Create test husband (John Smith)
INSERT INTO wp_family_members
(first_name, middle_name, last_name, gender, birth_date, clan_id, created_at, updated_at)
VALUES
('John', 'William', 'Smith', 'Male', '1970-05-15', @clan_id, NOW(), NOW());

SET @john_id = LAST_INSERT_ID();

-- Step 3: Create first wife (Mary Johnson)
INSERT INTO wp_family_members
(first_name, middle_name, last_name, maiden_name, gender, birth_date, clan_id, created_at, updated_at)
VALUES
('Mary', 'Ann', 'Smith', 'Johnson', 'Female', '1972-08-20', @clan_id, NOW(), NOW());

SET @mary_id = LAST_INSERT_ID();

-- Step 4: Create second wife (Sarah Wilson)
INSERT INTO wp_family_members
(first_name, last_name, maiden_name, gender, birth_date, clan_id, created_at, updated_at)
VALUES
('Sarah', 'Smith', 'Wilson', 'Female', '1975-03-10', @clan_id, NOW(), NOW());

SET @sarah_id = LAST_INSERT_ID();

-- Step 5: Create first marriage (John + Mary, divorced)
INSERT INTO wp_family_marriages
(husband_id, wife_id, marriage_date, marriage_location, marriage_order, marriage_status, divorce_date, end_date, end_reason, notes, created_at, updated_at)
VALUES
(@john_id, @mary_id, '1995-06-15', 'New York, NY', 1, 'divorced', '2005-03-20', '2005-03-20', 'divorce', 'First marriage, ended in divorce', NOW(), NOW());

SET @marriage1_id = LAST_INSERT_ID();

-- Step 6: Create second marriage (John + Sarah, married)
INSERT INTO wp_family_marriages
(husband_id, wife_id, marriage_date, marriage_location, marriage_order, marriage_status, notes, created_at, updated_at)
VALUES
(@john_id, @sarah_id, '2007-09-20', 'Las Vegas, NV', 2, 'married', 'Second marriage, currently married', NOW(), NOW());

SET @marriage2_id = LAST_INSERT_ID();

-- Step 7: Create children from first marriage
INSERT INTO wp_family_members
(first_name, last_name, gender, birth_date, parent1_id, parent2_id, parent_marriage_id, clan_id, created_at, updated_at)
VALUES
('Emily', 'Smith', 'Female', '1996-04-10', @john_id, @mary_id, @marriage1_id, @clan_id, NOW(), NOW()),
('David', 'Smith', 'Male', '1998-11-25', @john_id, @mary_id, @marriage1_id, @clan_id, NOW(), NOW());

-- Step 8: Create child from second marriage
INSERT INTO wp_family_members
(first_name, middle_name, last_name, gender, birth_date, parent1_id, parent2_id, parent_marriage_id, clan_id, created_at, updated_at)
VALUES
('Michael', 'James', 'Smith', 'Male', '2009-07-15', @john_id, @sarah_id, @marriage2_id, @clan_id, NOW(), NOW());

-- Display results with actual URLs you can use
SELECT
    CONCAT('✅ SUCCESS! John Smith created with ID: ', @john_id) as Message,
    CONCAT('http://family-tree.local/view-member?id=', @john_id) as 'View_John_URL',
    CONCAT('http://family-tree.local/view-member?id=', @mary_id) as 'View_Mary_URL',
    CONCAT('http://family-tree.local/view-member?id=', @sarah_id) as 'View_Sarah_URL';

-- Show what was created
SELECT
    'Marriages Created' as Type,
    COUNT(*) as Count,
    GROUP_CONCAT(id) as IDs
FROM wp_family_marriages
WHERE husband_id = @john_id;

SELECT
    'Children Created' as Type,
    COUNT(*) as Count,
    GROUP_CONCAT(CONCAT(first_name, ' ', last_name)) as Names
FROM wp_family_members
WHERE parent1_id = @john_id;
