-- Insert revision specific uuid for each row (pipeline_rev_uuid) 
UPDATE biocorepipe_save as pi SET pi.pipeline_rev_uuid = uuid() WHERE (pi.pipeline_rev_uuid IS NULL) OR (pi.pipeline_rev_uuid = '');
-- Insert revision specific uuid for each row (process_rev_uuid) 
UPDATE process as pi SET pi.process_rev_uuid = uuid() WHERE (pi.process_rev_uuid IS NULL) OR (pi.process_rev_uuid = '');
