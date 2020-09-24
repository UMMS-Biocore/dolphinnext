*************
Profile Guide
*************

This guide will walk you through all of your options within the Profile page.

Profile Page
============

Once logged in, click on the profile tab in the top right of the screen. You'll notice several tabs to explore in profile page.

.. image:: dolphinnext_images/profile_tabs2.png
	:align: center

* **Run environments:** This is your main segment for creating connection profiles. 
* **Groups:** where you can create group and add members to it to share your run or pipeline. 
* **SSH Keys:**, where you can create new or enter existing SSH key pairs to establish connection to any kind of host. 
* **Amazon Keys:** where you enter your Amazon Web Services (AWS) security credentials to start/stop `Amazon EC2 instances <https://aws.amazon.com/ec2>`_.
* **Google Keys:** where you enter your Google Cloud security credentials to execute your runs in Google Instances.
* **Github:** where you enter your Github security credentials to push your pipeline information to your Github account.
* **Change Password:** If you're not using google sign-in, you can change your password within this section.

.. tip:: Before creating run environment, **SSH Keys** needed to be created in SSH Keys tab. If you want to execute your runs in the cloud; you need to add Amazon or Google Key as well.


SSH Keys
========

.. raw:: html

    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; height: auto;">
        <iframe src="https://www.youtube.com/embed/7wH2NjXSebA" frameborder="0" allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
    </div>
    </br>


.. image:: dolphinnext_images/ssh_keys.png
	:align: center

In the SSH keys tab, you can create new or enter existing SSH key pairs by clicking on "Add SSH Key" button. Please enter the name of your keys and select the method you want to proceed: ``A. Use your own keys`` or ``B. Create new keys``.

* **A. Use your own keys:** If you choose "use your own keys", your private and public key pairs will be asked. You can reach your key pairs in your computer at default location: ``~/.ssh/id_rsa`` for private and ``~/.ssh/id_rsa.pub`` for public key. Copy those keys and paste into browser fields. If these files are not exist or you want to create new ones,  please check `the link here. <faq.html#how-can-i-create-ssh-keys-in-my-computer>`_

* **B. Create new keys:** You will proceed by clicking generate keys button where new pair of ssh keys will be specifically produced for you. 

After clicking save button, your information will be encrypted and kept secure. Now, you need to add your public key into ``~/.ssh/authorized_keys`` in the host to establish connection. Please check `Adding Public SSH Key <public_ssh_key.html>`_ guideline for help.


Amazon Keys
===========

.. image:: dolphinnext_images/profile_amazonkeys.png
	:align: center

In the Amazon keys tab, you can enter your AWS security credentials (access key, secret key and default region) by clicking on "Add Amazon Key" button. Your information will be encrypted and kept secure. Only you will have full access to view & change the key information.

.. note:: After saving your key, it is not allowed to view your keys for security purposes. However, you can always overwrite with a new key or delete it.


Google Keys
===========

.. image:: dolphinnext_images/profile_googlekeys.png
	:align: center

In the Google keys tab, you need to enter your Project ID and Service Account Key by clicking on "Add Google Key" button. 

* **Project ID:**   - Open the Google Cloud Console
                    - Go to Dashboard 
                    - Check Project info box for Project ID (eg. dolphinnext-193616)
                    
* **Service Account Key:**   - Open the Google Cloud Console
                             - Go to APIs & Services → Credentials
                             - Click on the Create credentials drop-down and choose Service Account Key, in the following page
                             - Select an existing Service account or create a new one if needed
                             - Select JSON as Key type
                             - Click the Create button and download the JSON file giving a name of your choice e.g. creds.json.

.. note:: After saving your key, it is not allowed to view your Service Account Key for security purposes. However, you can always overwrite with a new key or delete it.


Groups
======

.. image:: dolphinnext_images/profile_groups.png
	:align: center

Groups tab is used to create groups by clicking on "Create a Group" button. After creating group, you can add members by clicking ``Options > Add User`` button. By using this group information, you can share your process, pipeline or projects with group members. In order to see current members of the group, you can click ``Options > View Group Members`` button. You can also delete your group by clicking ``Options > Delete Group`` button.

Run Environments
================

.. image:: dolphinnext_images/profile_runenv.png
	:align: center

This section is used for defining connection profiles by clicking on "Add Environment" button. As a type, please choose one of these options: 

* **A. Host:** If you have an access to High Performance Computing (HPC) environments, or personal workstations. 
* **B. Amazon:** If you have an Amazon Web Services (AWS) account or planning to create one in order to run your jobs in the cloud. 
* **C. Google:** If you have an Google Cloud account or planning to create one in order to run your jobs in the cloud.

A. Defining Host Profile:
-------------------------
* **Username/Hostname:** You should enter your username and hostname of the host which you would like to connect (yourusername@yourhostname). For instance, for us2r@ghpcc06.umassrc.org::
    
        -  Username: yourusername (eg. us2r)
        -  Hostname: yourhostname (eg. ghpcc06.umassrc.org)

* **SSH Port (optional):** By default TCP port 22 is used for SSH connection. You can change this default by entering port number.

* **SSH Keys:** are saved in SSH keys tab and will be used while connecting to host.

* **Run Command (optional):** You may run the command or commands (by seperating each command with ``&&`` sign) before the nextflow job starts. eg.::

    source /etc/bashrc && module load java/1.8.0_77 && module load singularity/singularity-3.4.0
    
* **Singularity Cache Folder:** Directory where remote Singularity images are stored. By default home directory is used. Note: When using a computing cluster it must be a shared folder accessible from all computing nodes.

* **Profile Variables:** Since most of the pipelines uses genome reference and index files, you can define a download directory to keep these files. If multiple people are going to use DolphinNext, we would suggest using a shared path in your cluster. e.g.::
        
        params.DOWNDIR="/share/dolphinnext/downloads"
        
* **Nextflow Path (optional):** If nextflow path is not added to ``$PATH`` environment, you can define the path in this block. eg.::

    /project/umw_biocore/bin
    
* **Executor Settings:** In DolphinNext, there are 4 different section to control your executor settings. First two fields are defined in **profile -> run environment**, and rest of the fields are adjusted in the **advanced tab** of run page. If any option other than local and ignite, is selected, additional settings will be prompt for ``Queue/Partition``, ``Memory(GB)``, ``CPU`` and ``Time(min.)``.

    **1. Executor of Nextflow (in the profile -> run environment)**:

        Nextflow itself is initiated with this method. Currently, local, sge, slurm, and lsf executors are supported by DolphinNext to initiate nextflow. For `sge`, `slurm`, and `lsf` executors, it will be only used for running nextflow itself, so the time limit should be long enough to execute all of the processes in the pipeline. For `local` execution, it will limit the total amount of memory and CPU could be used by the run, so these values should be close enough to the maximum capacity of the memory and CPU. 
    
        - e.g. suggested parameters for sge/slurm/lsf: long (queue) 8 (GB Memory) 1 (CPU) 5000-8000 (min, Time)
        - e.g. suggested parameters for local: 100 (GB Memory) 8 (CPU)
    
    **2. Executor of Nextflow Jobs (in the profile -> run environment)**:

        This setting will be used as default setting for submitted jobs by Nextflow. If you don’t set any parameter in advanced section of your run page.
    
        - e.g. suggested parameters for sge/slurm/lsf: short (queue) 20 (GB Memory) 1 (CPU) 240 (min, Time)
        - e.g. suggested parameters for local/ignite: 20 (GB Memory) 1 (CPU) 
    
    **3. Executor Settings for All Processes (in the advanced tab of run page)**:

        This setting will overwrite Executor of Nextflow Jobs (in the profile) and set default setting for Nextflow Jobs. 
    
        - e.g. suggested parameters for sge/slurm/lsf: short (queue) 20 (GB Memory) 1 (CPU) 240 (min, Time)
        - e.g. suggested parameters for local/ignite: 20 (GB Memory) 1 (CPU)
    
    **4. Executor Settings for Each Process (in the advanced tab of run page)**:

        If particular process requires different parameters other than the defaults (which are defined in **Executor Settings for All Processes** or **Executor of Nextflow Jobs** sections), you can overwrite the general settings by clicking the checkbox of process that you want to change. This will only affect the settings of clicked process and keep the original settings for the rest of the processes.

        - e.g. suggested parameters for sge/slurm/lsf: long (queue) 20 (GB Memory) 4 (CPU) 1000-5000 (min, Time)
        - e.g. suggested parameters for local/ignite: 20 (GB Memory) 4 (CPU)

    .. note::  In case of non-standart resources or settings is required for executor, then you can specify these parameters by using **Other options** box. For instance, to submit SGE job with 3 CPU by using paralel environments, you may enter ``-pe orte 3`` (to use MPI for distributed-memory machines) or ``-pe smp 3`` (to use OpenMP for shared-memory machines) in the **Other options** box and just leave the CPU box empty.

B. Defining Amazon Profile:
---------------------------
* **SSH Keys:** are saved in SSH keys tab and will be used while connecting to host.
* **Amazon Keys:** AWS credentials that are saved in Amazon keys tab and will allow to start/stop Amazon EC2 instances.
* **Instance Type:** `Amazon EC2 instance types <https://aws.amazon.com/ec2/instance-types>`_ that comprise varying combinations of CPU, memory, storage, and networking capacity (eg. ``m3.xlarge``).
* **Image Id:** Virtual machine ID (eg. ``ami-032a33ebe57465518``). 

    If you want to create your own image, please install following programs: 
        - `Singularity <http://singularity.lbl.gov>`_
        - `Docker engine <https://www.docker.com/>`_ (version 1.11 or higher)
        - Apache Ignite with Cloud-init package
        - `Nextflow <https://www.nextflow.io/>`_ 
        - `AWS CLI <https://docs.aws.amazon.com/cli/latest/userguide/cli-chap-install.html>`_ 
        
        
* **Subnet Id/Security Group/Shared Storage Id/Shared Storage Mount:**

    The filesystem needs to be created at https://console.aws.amazon.com/efs/ and these informations will be obtained upon creation of shared file system. 
        * *Subnet Id:* Identifier of the VPC subnet to be applied e.g. subnet-05222a43. 
        * *Security Group:* Identifier of the security group to be applied e.g. sg-df72b9ba, default. 
        * *Shared Storage Id:* Identifier of the shared file system instance e.g. fs-1803efd1.
        * *Shared Storage Mount:* Mount path of the shared file system e.g. /mnt/efs.

    Please make sure following criterias are satisfied:
        1) Image has the directory to mount this storage.
        2) The output directory needs to be under this mount location.
        3) The storage system needs to be created in selected region and necessary rights need to be given in the console.
        4) EC2FullAccess and S3FullAccess permissions have added.

    .. warning::  Both EFS and images should be located in same location (eg. N. Virginia, Ohio etc.)

* **Default Working Directory:** Default directory in the host machine where runs will be executed. It is optional for AWS. (eg. /data/dnext)

* **Default Bucket Location for Publishing:** Default bucket location where dolphinnext reports will be published. It is optional for AWS (e.g. s3://bucket/dnext)

* **Run Command (optional):** You may run the command or commands (by seperating each command with ``&&`` sign) before the nextflow job starts. eg. ``source /etc/bashrc && module load java/1.8.0_31 && module load bowtie2/2.3.2``

* **Nextflow Path (optional):** If nextflow path is not added to ``$PATH`` environment, you can define the path in this block. eg. ``/project/umw_biocore/bin``

* **Singularity Cache Folder:** Directory where remote Singularity images are stored. By default home directory is used. Note: When using a computing cluster it must be a shared folder accessible from all computing nodes.

* **Profile Variables:** You can set commonly used pipeline variables here. For instance,``params.DOWNDIR`` is used in most of our public pipelines to save all genome related files (fasta, index etc.). So you can set this variable like this: ``params.DOWNDIR = "/share/dnext_data"`` Also, you can enter multiple variables by separating them with newline. 


* **Executor of Nextflow/Executor of Nextflow Jobs:** Amazon instances are automatically configured to use the Ignite executors. Therefore, while defining amazon profile, you should select ``local`` for **Executor of Nextflow** and ``ignite`` for **Executor of Nextflow Jobs.** 

C. Defining Google Profile:
---------------------------
* **SSH Keys:** are saved in SSH keys tab and will be used while connecting to host.
* **Google Keys:** Google credentials that are saved in Google keys tab and will allow to start/stop Google Cloud instances.
* **Zone:**  The Google zone where the computation is executed.(eg. us-east1-b)
* **Instance Type:** `Google Cloud machine types <https://cloud.google.com/compute/docs/machine-types>`_ that comprise varying combinations of CPU, memory, storage, and networking capacity (eg. ``n1-standard-4``).
* **Image Id:** Virtual machine ID (eg. ``dolphinnext-193616/global/images/dolphinnext-images-v1``). 

    If you want to create your own image, please install following programs: 
        - `Singularity <http://singularity.lbl.gov>`_
        - `Docker engine <https://www.docker.com/>`_ (version 1.11 or higher)
        - Apache Ignite with Cloud-init package
        - `Nextflow <https://www.nextflow.io/>`_ 
        - `gcloud <https://cloud.google.com/sdk/install>`_ 

* **Default Working Directory:** Default directory in the host machine where runs will be executed. It is mandatory for google cloud. (eg. /data/dnext)

* **Default Bucket Location for Publishing:** Default bucket location where dolphinnext reports will be published. It is mandatory for google cloud and you can always edit this path in the run page. (e.g. gs://bucket/dnext)
        
* **Run Command (optional):** You may run the command or commands (by seperating each command with ``&&`` sign) before the nextflow job starts. eg. ``source /etc/bashrc && module load java/1.8.0_31 && module load bowtie2/2.3.2``

* **Nextflow Path (optional):** If nextflow path is not added to ``$PATH`` environment, you can define the path in this block. eg. ``/project/umw_biocore/bin``

* **Singularity Cache Folder:** Directory where remote Singularity images are stored. By default home directory is used. Note: When using a computing cluster it must be a shared folder accessible from all computing nodes.

* **Profile Variables:** You can set commonly used pipeline variables here. For instance,``params.DOWNDIR`` is used in most of our public pipelines to save all genome related files (fasta, index etc.). So you can set this variable like this: ``params.DOWNDIR = "/share/dnext_data"`` Also, you can enter multiple variables by separating them with newline. 

* **Executor of Nextflow/Executor of Nextflow Jobs:** Google instances are automatically configured to use the Ignite executors. Therefore, while defining google profile, you can select ``local`` for **Executor of Nextflow** and ``ignite`` for **Executor of Nextflow Jobs.** 


GitHub Connection
=================

.. image:: dolphinnext_images/profile_github.png
	:align: center

You can enter your Github security credentials (Username, E-mail, Password) by clicking on "Add Github Account" button. Your information will be encrypted and kept secure. By adding your Github account, you could be able to push your pipeline information into your Github account and share with others.

Change Password
===============

.. image:: dolphinnext_images/profile_change_password.png
	:align: center

If you're not using google sign-in, you can change your DolphinNext password by using this section.
