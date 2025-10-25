-- Test Data: Create a member with marriages to demonstrate Phase 2 feature
-- Run this in Adminer or phpMyAdmin

-- Step 1: Create a test husband (John Smith)
INSERT INTO wp_family_members
(first_name, middle_name, last_name, gender, birth_date, clan_id, created_at, updated_at)
VALUES
('John', 'William', 'Smith', 'Male', '1970-05-15', 1, NOW(), NOW());

-- Get the ID of John (we'll use this)
SET @john_id = LAST_INSERT_ID();

-- Step 2: Create a test wife (Mary Johnson - first wife)
INSERT INTO wp_family_members
(first_name, middle_name, last_name, maiden_name, gender, birth_date, clan_id, created_at, updated_at)
VALUES
('Mary', 'Ann', 'Smith', 'Johnson', 'Female', '1972-08-20', 1, NOW(), NOW());

SET @mary_id = LAST_INSERT_ID();

-- Step 3: Create second wife (Sarah Wilson - second wife)
INSERT INTO wp_family_members
(first_name, last_name, maiden_name, gender, birth_date, clan_id, created_at, updated_at)
VALUES
('Sarah', 'Smith', 'Wilson', 'Female', '1975-03-10', 1, NOW(), NOW());

SET @sarah_id = LAST_INSERT_ID();

-- Step 4: Create first marriage (John + Mary, 1995-2005, divorced)
INSERT INTO wp_family_marriages
(husband_id, wife_id, marriage_date, marriage_location, marriage_order, marriage_status, divorce_date, end_date, end_reason, notes, created_at, updated_at)
VALUES
(@john_id, @mary_id, '1995-06-15', 'New York, NY', 1, 'divorced', '2005-03-20', '2005-03-20', 'divorce', 'First marriage, ended in divorce', NOW(), NOW());

SET @marriage1_id = LAST_INSERT_ID();

-- Step 5: Create second marriage (John + Sarah, 2007-present, married)
INSERT INTO wp_family_marriages
(husband_id, wife_id, marriage_date, marriage_location, marriage_order, marriage_status, notes, created_at, updated_at)
VALUES
(@john_id, @sarah_id, '2007-09-20', 'Las Vegas, NV', 2, 'married', 'Second marriage, currently married', NOW(), NOW());

SET @marriage2_id = LAST_INSERT_ID();

-- Step 6: Create children from first marriage
INSERT INTO wp_family_members
(first_name, last_name, gender, birth_date, parent1_id, parent2_id, parent_marriage_id, clan_id, created_at, updated_at)
VALUES
('Emily', 'Smith', 'Female', '1996-04-10', @john_id, @mary_id, @marriage1_id, 1, NOW(), NOW()),
('David', 'Smith', 'Male', '1998-11-25', @john_id, @mary_id, @marriage1_id, 1, NOW(), NOW());

-- Step 7: Create child from second marriage
INSERT INTO wp_family_members
(first_name, middle_name, last_name, gender, birth_date, parent1_id, parent2_id, parent_marriage_id, clan_id, created_at, updated_at)
VALUES
('Michael', 'James', 'Smith', 'Male', '2009-07-15', @john_id, @sarah_id, @marriage2_id, 1, NOW(), NOW());

-- Display the results
SELECT 'Created test data successfully!' as Status;
SELECT @john_id as 'John_Smith_ID', @mary_id as 'Mary_Johnson_ID', @sarah_id as 'Sarah_Wilson_ID';
SELECT * FROM wp_family_marriages WHERE husband_id = @john_id;
SELECT id, first_name, last_name, parent_marriage_id FROM wp_family_members WHERE parent1_id = @john_id;
