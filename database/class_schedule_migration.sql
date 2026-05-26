-- Class Schedule Table Migration
-- Run this against your student_management_system database

USE `student_management_system`;

CREATE TABLE IF NOT EXISTS `class_schedule` (
  `id`          int(11)      NOT NULL AUTO_INCREMENT,
  `teacher_id`  int(11)      NOT NULL,
  `course_id`   int(11)      NOT NULL,
  `dept_id`     int(11)      NOT NULL,
  `section`     varchar(10)  NOT NULL DEFAULT 'A',
  `date`        date         NOT NULL,
  `time_start`  time         NOT NULL,
  `time_end`    time         NOT NULL,
  `created_at`  timestamp    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`)  REFERENCES `courses`(`id`)  ON DELETE CASCADE,
  FOREIGN KEY (`dept_id`)    REFERENCES `departments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
