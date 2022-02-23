*********
Run Guide
*********

In the previous section (`project guide <project.html>`_), we saw the creation of project and adding pipelines to it. In this section, we will investigate the run settings to initiate our new run.

Basics
======
In the header of the run page, you will notice the rocket icon and the title of the run where you can also edit your run name. Tracking of your run is facilitated by project and pipeline links which are located just next to run name as shown at below:

.. image:: dolphinnext_images/run_header.png
	:align: center

Similar to `pipeline <pipeline.html>`_ section, **Save**, **Download Pipeline**, **Copy Run** and **Delete Run** icons are found in the header section to manage your run. Besides, optional **Run Description** section is exist just below the header section.

Run Status
==========
Run status is monitored at the right part of the header. Initially, orange ``Waiting`` button is shown. In order to initiate run, following data need to be entered:

    1. **Work Directory:**  Full path of the directory, where nextflow runs will be executed.
    2. **Run Environment:** Profile that is created in the `profile <profile.html>`_  page. If `Amazon profile <profile.html#b-defining-amazon-profile>`_ or `Google profile <profile.html#c-defining-google-profile>`_ is selected, then status of the profile should to be at the stage of **running**.
    3. **Inputs:** Value and path of the files need to be entered.
    
.. warning:: If amazon s3 path is entered as a input or publish directory path, amazon keys (which will appear in the **Run Setting** section) need to be also selected.

All available status messages are listed at table below:

=========== =========================================================================================================================
Status      Meaning                 
=========== =========================================================================================================================
Waiting     Waiting for inputs, output directory and selection of active environment (\*amazon keys, if s3 path is used)
Ready       Ready to initiate run     
Connecting  Sending SSH queries to selected host system
Waits       Job is submitted, waits for the execution
Running     Nextflow is executed and running the jobs.
Completed   Nextflow job is completed.
Run Error   Error occured before submiting the jobs or while executing the jobs.
Terminated  User terminated the run by using "terminate run" button.
=========== =========================================================================================================================

Run Settings
============

* **Work Directory:** Full path of the directory, where all nextflow runs will be executed. Example path::
    
    /home/newuser/workdir

* **Run Environment:** Profile that is created in the `profile <profile.html>`_  page. If `Amazon profile <profile.html#b-defining-amazon-profile>`_ or `Google profile <profile.html#c-defining-google-profile>`_ is selected, then status of the profile should to be at the stage of **running**.


* **Use Docker Image:** Nextflow supports the Docker containers which allows you to create fully reproducible pipelines. Docker image can contain any kind of software that you might need to execute your pipeline. It works transparently and output files are created in the host system without requiring any addition step. The only requirement is the `installation of the Docker <https://docs.docker.com/install/>`_ on the execution platform.  To activate this feature in DolphinNext just click the "Use Docker Image" checkbox and enter following information:
    
    1. **Image:** Docker image name. Example::
        
        UMMS-Biocore/dolphinnext-studio
    
    2. **RunOptions (optional):** You can enter any command line options supported by the docker run command. Please click `this docker link <https://docs.docker.com/engine/reference/commandline/cli/>`_ for details.

* **Use Singularity Image:** Alternative to Docker, you can activate singularity image by clicking "Use Singularity Image" checkbox and entering relevant fields. The only requirement is the `installation of the Singularity <http://singularity.lbl.gov/docs-installation/>`_ on the execution platform.
    
    1. **Image:** Path to sigularity image. Example::
        
        shub://UMMS-biocore/singularitysc
        /project/umw_biocore/singularity/UMMS-Biocore-singularity-master.simg
    
    2. **RunOptions (optional):** You can enter any command line options supported by the ``singularity exec``. Please click `link for details <http://singularity.lbl.gov/docs-usage/>`_. For instance, you can mount the directories by using ``--bind command``.  Example::
        
        --bind /project:/project --bind /nl:/nl --bind /share:/share
    
    .. tip:: Mounting directories in singularity requires you to create the directories in the image beforehand.
    

Advanced Options
================

* **Run Command (optional):** You may run the command or commands (by seperating each command with ``&&`` sign) before the nextflow job starts. eg:: 

    source /etc/bashrc && module load java/1.8.0_31 && module load bowtie2/2.3.2

* **Publish Directory:** Work directory is default publish directory for DolphinNext. If you want to enter new publish directory, just click this item and enter the full path of publish directory. Local paths (eg. ``/home/user/test``), amazon s3 paths (eg. ``s3://yourbucket/test``) or google storage paths (eg. ``gs://yourbucket/test``) are accepted.


* **Executor Settings:** 

    **1. Executor Settings for Nextflow (in the profile)**:
    You can determine the system where nextflow itself is initiated. Currently local, sge, slurm and lsf executors are supported by DolphinNext to initiate nextflow and it will be only used for running nextflow itself. 
    e.g. suggested parameters: long 8GB 1CPU 5000-8000min
    
    **2. Executor of Nextflow Jobs (in the profile)**:
    This setting will be used if you don’t set any parameter in advanced section of your run page. If any option other than local and ignite, is selected, additional settings will be prompt for ``Queue/Partition``, ``Memory(GB)``, ``CPU`` and ``Time(min.)``. Adjustment of these parameters are allowed for both options.
    e.g. suggested parameters: short 20GB 1CPU 240min
    
    **3. Executor Settings for All Processes (in Advanced tab of run page)**:
    This setting will overwrite Executor of Nextflow Jobs (in the profile). 
    e.g. suggested parameters: short 20GB 1CPU 240min
    
    **4. Executor Settings for Each Process (in Advanced tab of run page)**:
    If particular process needs special parameters other than **executor settings for all processes**, you may override general settings by clicking the checkbox of process that you want to change. This will only affect the settings of clicked process and keep the original settings for the rest.
    e.g. suggested parameters: long 20GB 4CPU 1000-5000min


* **Delete intermadiate files after run:** This is default settings for DolphinNext to keep only selected output files in the work/publish directory and removing the rest of the files. Here the main goal is to minimize the required space for each project.

* **Permissions and Groups:** By default, all new runs are only seen by the owner. However, you can share your run with your group by changing permissions to "Only my groups" and choose the group you want to share from **group selection** dropdown. 


Pipeline Files
==============

This section is separated into two groups: **inputs** and **outputs**. 

* **Inputs:** The input file paths or values are entered by clicking **Select File** or **Enter Value** button. In order to select **multiple files**, wildcard characters ``*``, ``?``, ``[]`` and ``{}`` should be used. These arguments are interpreted as a `glob <https://docs.oracle.com/javase/tutorial/essential/io/fileOps.html#glob>`_ path matcher by Nextflow and returns a list of paths that are matching the specified pattern. Several examples to define inputs are listed below:

=========== ================================
Input Type  Example                 
=========== ================================
File/Set    /share/data/mm10.fa  
File/Set    /share/validfastq/\*_{1,2}.fastq  
Val         pair     
Val         ~/scripts/filter.py     
=========== ================================

* **Outputs:** When the run successfully completes, the path of the output files will be appeared in this region. 

Workflow
========

To give you an overview, overall pipeline and its modules are showed in this region. You can also reach the process contents after clicking the **go to pipeline** link.

Run Logs
========

Log section keeps track of each run logs which is initiated by clicking **Ready to Run** button. You can monitor each step of the run both before and after nextflow execution as shown at figure below. 

.. image:: dolphinnext_images/run_log.png
    :align: center
    
You can view various log files such as timeline.html, dag.html, trace.txt, .nextflow.log, nextflow.nf, nextflow.config as shown at below:

timeline.html:

.. image:: dolphinnext_images/timeline.png
    :align: center
    :width: 99%
    
dag.html:

.. image:: dolphinnext_images/dag.png
    :align: center
    :width: 99%
    
trace.txt:
    
.. image:: dolphinnext_images/trace.png
    :align: center
    :width: 99%
    
.nextflow.log:
    
.. image:: dolphinnext_images/nextflowlog.png
    :align: center
    :width: 99%

nextflow.nf:

.. image:: dolphinnext_images/nextflownf.png
    :align: center
    :width: 99%

nextflow.config:

.. image:: dolphinnext_images/nextflowconfig.png
    :align: center
    :width: 99%


If any error occured on any of these steps, detailed explanation about the error will be displayed in this section and run error sign will appear in the right side of the header as show in the example below: 

.. image:: dolphinnext_images/run_error.png
    :align: center


Reports
=======

Reports tab will be appear in the run page as soon as run is initiated by clicking **Ready to Run** button. You can view output files in various modules such as R-Markdown, Datatables, Highcharts, HTML or PDF viewer. Please check the example report section of RSEM pipeline at below.

.. image:: dolphinnext_images/report_all.png
    :align: center
    :width: 99%

Each report row corresponds to output parameter in the pipeline workflow and you can easily visualize their content by clicking on each row. All these sections have download, full screen, and open in new window icons in order to help you to investigate each report.

.. note:: If you want to integrate your visualization tool into DolphinNext, please let us know about it (biocore@umassmed.edu). We can add this feature for you.

* **DEBrowser:**

DEBrowser is a R library to provide an easy way to perform and visualize DE analysis. This module takes count matrices as input and allows interactive exploration of the resulting data. You can reach their documentation by clicking `DEBrowser link <https://bioconductor.org/packages/release/bioc/vignettes/debrowser/inst/doc/DEBrowser.html>`_. 

.. image:: dolphinnext_images/report_debrowser.png
    :align: center
    :width: 99%

* **R Markdown:**

R Markdown feature provides interactive analysis of the produced data. We have prepared series of R Markdown reports which will allow you to reach your report in a HTML or PDF format as soon as your run complete. Within an R Markdown (.RMD) file, R Code Chunks can be embedded with the native Markdown syntax for fenced code regions. For example, the following code chunk computes a data histogram and renders a bar plot as a PNG image:

.. image:: dolphinnext_images/report_rmarkdown.png
    :align: center
    :width: 99%

You can reach the details about R Markdown in their web page by clicking `rmarkdown link <https://rmarkdown.rstudio.com/>`_. 

At the top of R-Markdown module, there are several icons which will help you to edit your rmd file, save as a new file and download in various formats such as RMD, PDF or HTML. In order to facilitate the review process, you can click "full screen" icon to fit the module in your screen. Besides you can adjust **Auto Updating Output** and **Autosave** features by clicking settings icon.

        * **Auto Updating Output:** If enabled, the preview panel updates automatically as you code. If disabled, use the "Run Script" button to update.
    
        * **Autosave:** If active, DolphinNext will autosave the file content every 30 seconds.


* **Datatables:**

This module powered by `Datatables <https://datatables.net//>`_ which allows you view, sort and search in the table content. Please check following two examples where alignment and RSEM summaries are shown.

        * Alignment Summary:

        .. image:: dolphinnext_images/report_datatables2.png
            :align: center
            :width: 99%


        * RSEM Summary:


        .. image:: dolphinnext_images/report_datatables.png
            :align: center
            :width: 99%
    
This module allows you to fit the table in your screen by clicking "full screen" icon on top of the module.

* **HTML Viewer:**

You can easily embed html content in to our report section by using HTML viewer. Please check the example for MultiQC output at below:

.. image:: dolphinnext_images/report_html.png
    :align: center
    :width: 99%
    
    

* **PDF Viewer:**

Similar to HTML Viewer, PDF files can be embeded in report section. You can see the piPipes report as an example at below:

.. image:: dolphinnext_images/report_pdf.png
    :align: center
    :width: 99%

