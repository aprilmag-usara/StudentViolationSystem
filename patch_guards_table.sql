USE student_violation_system;

-- Add guard_rank and schedule columns to guards table if they don't exist
ALTER TABLE guards 
ADD COLUMN IF NOT EXISTS guard_rank VARCHAR(50) DEFAULT 'I',
ADD COLUMN IF NOT EXISTS schedule VARCHAR(100) DEFAULT 'Full Time';
