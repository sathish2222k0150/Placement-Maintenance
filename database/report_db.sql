CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff') NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `project` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `batches` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `project_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `course_name` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `reg_no` varchar(50) DEFAULT NULL,
  `aadhar` varchar(12) DEFAULT NULL,
  `boarding_lodging` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `father_or_husband_name` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `qualification` varchar(100) DEFAULT NULL,
  `religion` varchar(255) NOT NULL,
  `caste` varchar(50) DEFAULT NULL,
  `annual_income` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `village` varchar(100) DEFAULT NULL,
  `mandal` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `alt_contact` varchar(15) DEFAULT NULL,
  `batch_end` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `placement_initial` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date_of_joining` date DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `salary_per_month` varchar(50) DEFAULT NULL,
  `other_perks` text DEFAULT NULL,
  `organization_name` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `organization_address` text DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `office_contact_number` varchar(20) DEFAULT NULL,
  `status` enum('Yes','No') DEFAULT 'No',
  `remarks` VARCHAR (100) DEFAULT Null,
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `placement_second_stage` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `placement_initial_id` int(11) DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `salary_per_month` varchar(50) DEFAULT NULL,
  `other_perks` text DEFAULT NULL,
  `organization_name` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `organization_address` text DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `office_contact_number` varchar(20) DEFAULT NULL,
  `status` enum('Agreed','Not Agreed') DEFAULT 'Agreed',
  `remarks` VARCHAR (100) DEFAULT Null,
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `placement_final_stage` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `placement_second_stage_id` int(11) DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `salary_per_month` varchar(50) DEFAULT NULL,
  `other_perks` text DEFAULT NULL,
  `organization_name` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `organization_address` text DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `office_contact_number` varchar(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
