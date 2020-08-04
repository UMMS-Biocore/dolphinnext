******************
Google Cloud Guide
******************

DolphinNext supports submitting jobs to the Google cloud by using Nextflow. It allows you to practically setup, start/stop a computing cluster and run your pipeline in the Google cloud infrastructure.


Configuration
=============
Once logged in, click on the profile tab in the top right of the screen. Both `SSH <profile.html#ssh-keys>`_ and `Google Keys <profile.html#google-keys>`_ need to be entered in each tab. Then you can proceed with creating `google run environment <profile.html#c-defining-google-profile>`_ in the run environments tab. After creating run environment "start/stop" button will appear in actions column of your google profile as shown in the figure below:

.. image:: dolphinnext_images/google_start.png
    :align: center


Clicking on the start/stop button will open new window called **Google Management Console**.

Google Management Console
=========================

Starting and stoping Google cloud is controled in Console management console. There are two ways to open console. First option is clicking following button: ``profile > run environments > Start/Stop``. Altervatively, you can quickly reach Google Console by clicking "Google" icon at the top of the screen. The number of active profile is displayed with green tag at the top of the google console button. 

.. image:: dolphinnext_images/google_quick.png
    :align: center
    :width: 25%


State of your profiles will be shown as below:


.. image:: dolphinnext_images/google_console.png
    :align: center

Starting Cluster
================
In order to active Google cluster, click on "start" button of the profile you wanted to initiate. Following options will be prompted.

* **Nodes:** Enter the number of instances, you want to initiate. First node is created as ``master``, and the remaining as ``workers``.

* **Use Autoscale:**  This is Nextflow's critical feature which allows the cluster to adapt dynamically to the workload by changing computing recources. After clicking this option and entering number of **Maximum Instances**, new instances will be automatically added to the cluster when tasks wait for 5 minutes in pending status. The upper limit should be entered by **Maximum Instances** to control the size of cluster. By default unused instances are removed when they are not utilised.

* **Auto Shutdown:** Google instance will be automaticaly shutdown when there is no ongoing run for 10 minutes. Note that this feature will be activated after you initiate your first run.

Profile status will be updated as ``Waiting for reply`` as soon as you click the "Activate Cluster" button. If your credentials and profile are correct, profile status will change to ``Initializing`` and ``Running``, respectively. However, in case of missing or wrong profile information, status will turn into ``Terminated`` and reason of the error will appear next to the status. All available states of the profile are listed in table below:


======================= ================================================================
Status                  Meaning
======================= ================================================================
Inactive                Google cloud has not initiated yet.
Waiting for reply       Cluster request is sent.
Initializing            Cluster request is accepted and nodes are initializing.
Running                 Google cloud ready to submit jobs.
Waiting for termination Cluster termination request is sent and waiting for termination.
Terminated              Google cloud has terminated.
======================= ================================================================


Once the cluster initialization is complete, ``user@hostname`` will appear next to the ``running`` status as shown in figure below.

.. image:: dolphinnext_images/google_running.png
    :align: center

You may connect to the master node by using the following SSH command::

    ssh user@hostname

Submit a Job
============
It is similar to regular job submission, please follow these steps:

1. `Select your pipeline and add into a project <project.html>`_
2. `Initiate run <run.html>`_

On the run page, you should select your active google profile as a `Run Environment <run.html#run-settings>`_ and click "Ready to Run" button.

Stoping Cluster
===============
When runs are complete, you can stop cluster by clicking "stop" button on Google Management Console. Profile status will be updated as ``Waiting for termination``, and in few seconds it will be changed to ``Terminated`` as soon as confirmation is received.
