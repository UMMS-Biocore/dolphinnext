API v1
======

DolphinNext API responses are in JSON format includes errors
and HTTP response status codes to designate success and failure.

.. contents:: Table of contents
   :local:
   :backlinks: none
   :depth: 3


Runs
----

Get All Runs 
~~~~~~~~~~~~

.. http:get:: {{URL}}/api/service.php?data=getRuns

    Retrieve a list of all existing runs for the current logged in user.

    **Example request**:

    .. sourcecode:: bash

        $ curl -X GET 'https://dolphinnext.umassmed.edu/api/service.php?data=getRuns' \
        -H 'Authorization: Bearer <your-access-token>'

    **Example response**:

    .. sourcecode:: json

        {
            "status": "success",
            "data": {
                "data": [
                    {
                        "name": "Build index (amazon)",
                        "_id": "234"
                    },
                    {
                        "name": "UMIextract",
                        "_id": "249"
                    },
                    {
                        "name": "test",
                        "_id": "250"
                    }
                ]
            }
        }

 



Get a Run 
~~~~~~~~~

.. http:get:: /api/service.php?data=getRun&id=(string:run_id)

    Retrieve details of a single run.

    **Example request**:

    .. sourcecode:: bash

        $ curl -X GET \
        'https://dolphinnext.umassmed.edu/api/service.php?data=getRun&id=4311' \
        -H 'Authorization: Bearer <your-access-token>'

    **Example response**:

    .. sourcecode:: json

         {
             "status": "success",
             "data": {
                 "data": {
                     "inputs": [
                         {
                             "name": "gtfFilePath",
                             "type": "val",
                             "val": "/share/data/umw_biocore/genome_data/human/hg38_gencode_v34/ucsc.gtf"
                         },
                         {
                             "name": "mate",
                             "type": "val",
                             "val": "triple"
                         },
                         {
                             "name": "genome_build",
                             "type": "val",
                             "val": "human_hg38_gencode_v34"
                         }
                     ],
                     "dmetaOutput": [
                         {
                             "filename": "filename",
                             "feature": "row",
                             "target": "analysis",
                             "id": "g-109",
                             "name": "UMI_count_final_after_star"
                         },
                         {
                             "filename": "row",
                             "feature": "column",
                             "target": "sample_summary",
                             "id": "g-124",
                             "name": "summary"
                         }
                     ]
                 }
             }
         }


Create a Run 
~~~~~~~~~~~~~~~~

.. http:post:: /api/service.php?run=startRun

    Create a new run.

    **Example request**:

    .. sourcecode:: bash

        $ curl -X POST \
        'https://dolphinnext.umassmed.edu/api/service.php?run=startRun' \
        -H 'Authorization: Bearer <your-access-token>' \
        -H 'Content-Type: application/json' \
        -d '
            {
                "doc": {
                          "name": "CB017_H2_V1_Bst_sc_iD_S1",
                          "tmplt_id": 3535,
                          "in": {
                              "reads": {
                                 "name": "indroptest",
                                 "file_env": "ghpcc06.umassrc.org",
                                 "files_used": [
                                     [
                                         "VB74_NL2_S1_L001_R1_001.fastq.gz",
                                         "VB74_NL2_S1_L001_R2_001.fastq.gz",
                                         "VB74_NL2_S1_L001_R3_001.fastq.gz"
                                     ],
                                     [
                                         "VB74_NL2_S1_L002_R1_001.fastq.gz",
                                         "VB74_NL2_S1_L002_R2_001.fastq.gz",
                                         "VB74_NL2_S1_L002_R3_001.fastq.gz"
                                     ]
                                 ],
                                 "file_dir": [
                                     [
                                         "/project/indroptest"
                                     ]
                                 ],
                                 "archive_dir": "",
                                 "s3_archive_dir": [],
                                 "gs_archive_dir": [],
                                 "collection_type": "triple",
                                 "file_type": "fastq",
                                 "collection_name": "Vitiligo experiment",
                              },   
                              "mate": "triple"
                          },
                          "out": {
                              "sample_summary": {}
                          },
                          "run_env": "ghpcc06.umassrc.org",
                          "work_dir": "/run_work_dir"
                       },
                 "info": {
                           "dmetaServer": "https://dmeta-skin.dolphinnext.com"
                           "project": "vitiligo"
                       } 
            }'

    **Example response**:

    .. sourcecode:: json

            {
                "status": "success",
                "data": {
                    "data": {
                        "status": "submitted",
                        "id": 354,
                        "creationDate": "2021-04-13T05:32:16.505Z",
                        "message": "Run successfully submitted."
                    }
                }
            }

