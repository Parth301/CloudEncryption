CREATE TABLE `user_files` (
  `file_id` int(11) PRIMARY KEY,
  `user_email` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_data` longblob NOT NULL,
  `encryption_key` varchar(255) NOT NULL,
  `iv` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
);
