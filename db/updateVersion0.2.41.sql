-- Insert uuid for each pipeline_gid 
UPDATE biocorepipe_save as pi, 
(
    SELECT DISTINCT pip.id, pip.pipeline_gid, pip.pipeline_uuid
               FROM biocorepipe_save pip
               INNER JOIN (
                SELECT p.pipeline_gid, MAX(p.id) id
                FROM biocorepipe_save p
                GROUP BY p.pipeline_gid
                ) b ON pip.id = b.id AND pip.pipeline_gid=b.pipeline_gid
) as temp
SET pi.pipeline_uuid = uuid() WHERE temp.id = pi.id;

-- Copy uuid to each revision of pipeline_gid 

UPDATE biocorepipe_save t, (SELECT DISTINCT id, pipeline_gid, pipeline_uuid
                        FROM biocorepipe_save
                       WHERE pipeline_uuid IS NOT NULL AND pipeline_uuid != '') t1
   SET t.pipeline_uuid = t1.pipeline_uuid
 WHERE t.pipeline_gid = t1.pipeline_gid;


-- Insert uuid for each process_gid 
UPDATE process as pi, 
(
    SELECT DISTINCT pip.id, pip.process_gid, pip.process_uuid
               FROM process pip
               INNER JOIN (
                SELECT p.process_gid, MAX(p.id) id
                FROM process p
                GROUP BY p.process_gid
                ) b ON pip.id = b.id AND pip.process_gid=b.process_gid
) as temp
SET pi.process_uuid = uuid() WHERE temp.id = pi.id;

-- Copy uuid to each revision of process_gid 

UPDATE process t, (SELECT DISTINCT id, process_gid, process_uuid
                        FROM process
                       WHERE process_uuid IS NOT NULL AND process_uuid != '') t1
   SET t.process_uuid = t1.process_uuid
 WHERE t.process_gid = t1.process_gid;