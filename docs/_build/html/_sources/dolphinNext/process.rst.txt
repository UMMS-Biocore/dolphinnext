*************
Process Guide
*************

This guide will walk you through the creation of DolphinNext process. 

Process Window
==============

Once logged in, click on the pipeline tab in the top left of the screen. You'll notice several button at the left sidebar menu. New processes are created by clicking green "New process" button.

.. image:: dolphinnext_images/process_buttons.png
	:align: center
	:width: 35%

Basics
======
Once you clicked green "New process" button, new window will appear to define process components.

* **Name:** Process name is entered in this block. After creation of process, this name will appear in the left sidebar menu under the selected *menu group*.

* **Description:** An explanation of how process works is described in this region.

* **Menu group:** Selection of menu group which categorizes the processes in the left sidebar menu. If it is required, you may add new menu groups by clicking "add menu group" button. Afterwards, you may edit or delete these new groups by "edit menu group" or "delete menu group" buttons. 

Parameters
==========

This section is used to create parameters which will be used while defining *inputs* or *outputs*. New parameters are created by clicking "add parameter" button.

.. image:: dolphinnext_images/process_addparameter.png
	:align: center

* **Identifier:** Identifier is simply parameter name and allows you to call same parameters in other processes.

.. note:: When qualifier set to ``val``, ``identifier`` is used to filter available nodes while connecting each nodes. However, when ``file`` or ``set`` is selected as qualifier, ``file type`` is used for filtering available nodes.

* **Qualifier:** Three main type of qualifiers (``file``, ``set`` and ``val``) are exist in DolphinNext:  
    1) **File:** Uses the received value as a file. Example usage in nextflow file::
    
        file genome
    
    
    2) **Set:** Allows to handle a group of input values having other qualifiers. Example usage::
    
        set val(name), file(genome) 
    
    
    3) **Val:** Allows to access the received input value by its name in the process script. Example usage::
    
        val script_path
    
    
* **File type:** If qualifier is set to ``file`` or ``set``, file type option will appear. This option will be used to filter available nodes while generating pipelines. 

.. tip:: For instance, you may create ``genome`` parameter by entering identifier as:``genome``, qualifier: ``file`` and file type: ``fasta``. Similarly for creating ``script_path`` parameter you can define identifier as:``script_path`` and qualifier: ``val``.  

Inputs
======
    
This section where you enter all of your input nodes of the process. You can start adding by clicking "Add input..." dropdown. After adding selected parameter as a input node, "input name" box and "add operator" button will appear. 

.. image:: dolphinnext_images/process_inputs.png
	:align: center

Input name box used to define nextflow variables which will be used in the `scripts <process.html#id4>`_ section. For instance, if you enter input name as ``genome``, in the scripts section you can recall this variable as ``${genome}``. Other examples are listed in the following table:

=========== ========================== ====================
Qualifier   Input name                 Recall in the Script
=========== ========================== ====================
val         script_path                ${script_path}
file        genome                     ${genome}
set         val(name), file(genome)    ${genome}
set         val(name), file(genome)    ${name}
=========== ========================== ====================

Additionally, if you need to transform values emitted by a channel, you can click "operators" button and select operators from dropdown. For detailed information, you can continue to read `operators <process.html#id5>`_ section.

Outputs
=======

The output nodes are defined in this section. Similar to adding inputs, by clicking "Add output..." dropdown and selecting output parameter will show `output name` box, `add operator` and `add regular expression` buttons.

.. image:: dolphinnext_images/process_outputs.png
	:align: center

Output files, created by the process, are grabbed by "output name" box. By entering the pattern of the output files eg. ``genome.index*`` would grab the files that are starts with ``genome.index``. Besides you can use nextflow variables which are defined in `inputs <process.html#inputs>`_ or `scripts <process.html#id4>`_ section. As an example, if you enter input name as ``genome``, in the outputs section you can recall this variable as ``'${genome}_out.txt'``. Other examples are listed in the following table:

=============== ========================== ================ ====================================
Input Qualifier Input name                 Output Qualifier Output name
=============== ========================== ================ ====================================
file            genome                     file             "${genome}_out.txt"                 
set             val(name), file(reads)     set              val(name), file("${name}.unmap*.fq")
set             val(name), file(reads)     file             "${name}.bam"
set             val(name), file(genome)    file             "genome.index*"
=============== ========================== ================ ====================================

Additionally, if you need to transform values emitted by a channel, you can click "operators" button and select operators from dropdown. For detailed information, you can continue to read `operators <process.html#id5>`_ section. In addition to operators, regular expressions can be activated by clicking "regular expressions" button. You can learn more about this section by clicking `regular expressions <process.html#id6>`_ section.

Scripts
=======

Main process scripts are defined in this region. Three type of mode are available in Nextflow: A. Script B. Shell C. Exec. These statements defines how the command going to be executed by the process.

**A. Script:**

For simplicity, DolphinNext uses script format by default, and accepts each line as a command. It is same as using three double quotes ``"""`` at the start and the end of the command block. If you use three double quotes, DolphinNext will take that particular area as command block. Therefore, following two strings will be executed as same::
    
    """ tophat2 -o . ${indexPath} $reads """
    
    tophat2 -o . ${indexPath} $reads
   

Each line is executed as a BASH script in the host. It can be any command or script that is typically used in terminal shell or BASH script.

.. image:: dolphinnext_images/process_script.png
	:align: center
    
* **Conditional Scripts:**

Conditional scripts can be used with control statements such as if, switch etc. In order to use these feature, you must start the code block by using ``script:`` keyword. In this way, the interpreter will evaluate all the following statements as a code block and find the script string to be executed. Please check the following example::
    
        script:
        name =  reads.toString() - '.fastq'
       
        if (mate == "pair") {
            """
            bowtie2 -x genome.index -1 ${reads.join(' -2 ')} -S ${name}_alignment.sam --un-conc ${name}_unalignedreads
            """
        }
        else if (mate == "single") {
            """
            bowtie2 -x genome.index -U $reads -S ${name}_alignment.sam --un ${name}_unalignedreads
            """
        }
        
.. tip:: As shown in the example above, it is possible to run Bowtie based on mate status of the reads. In order to activate this property, you must add ``mate`` parameter as an input. While you are running the pipeline, you may select ``single`` or ``pair`` option according to your needs.
    
    
**B. Shell:**

Alternatively, you can use ``shell`` block where Nextflow variables are declared by exclamation mark ``!``. This feature allows you to use both Nextflow and BASH variables in the same code without using escape character. In order to use shell mode, you start code by adding ``shell:`` and add three single quotes (``'''``) at the start and the end of the command block::

    shell:
    '''
    echo $PATH and !{new_path}
    '''

**C. Exec:**

Nextflow processes allows you to execute native code other than system commands. This way you may easily follow local Nextflow variables. To start exec mode, you can add ``exec:`` block at the beggining of the script::

    exec:
    println "${genome}"
    
    
Operators
=========

If you need to transform values emitted by a channel, you can click "operators" button and select operators from dropdown. Optionally, operator content could be specified to adjust their act. Besides, multiple operators can be added by starting paranthesis ``(``. Examples are listed in the following table:  

============= ==================== ======================================================================================
Operator      Operator Content     Usage
============= ==================== ======================================================================================
flatMap       ().buffer(size:3)    Groups 3 emitted item into one new channel.            
mode flatten                       To emit each output file as a sole item      
groupTuple                         Collects tuples (or lists) of values emitted and groups them based on their key value.
============= ==================== ======================================================================================

.. tip:: To get more information about operators, you can use `this link <https://www.nextflow.io/docs/latest/operator.html>`_ to reach Nextflow documentation.


Regular Expressions
===================

This is optional regular expresion feature to filter output files. By default DolphinNext uses **output name** box pattern to decide which files are going to be transferred to output directory. If this feature is not sufficient or additional filtration is required, then regular expression feaure might be activated by clicking "Regular Expressions" button at the outputs section. Example usage:

.. image:: dolphinnext_images/process_regex.png
	:align: center
	:width: 35%


Header Script
=============

This section allows you to add additional scripts or comments before process block starts. This way you may recall same function several times in the script section.

.. image:: dolphinnext_images/process_headerscript.png
	:align: center


Process Options
===============

You may separate your main process inputs and optional parameters by using **Process Options** feature. On the run page, these parameters will be asked separately in the Process Options section as in the image shown at below:

.. image:: dolphinnext_images/process_processopt.png
	:align: center

In order to create these form, you need to use following syntax in the **process header**::
    
    variableName = defaultValue //* @formType @description:"..." @tooltip:"..." @options:"..."

.. note:: You can define defaultValue with single/double quotes (for strings) or without any quotes (for numbers).


* **@formType:** Four type of form fields are available in DolphinNext (``@input``, ``@textbox``, ``@checkbox``, ``@dropdown``):  
    
    1) **@input:** It creates single-line text field. Example usage and created form field in run page::
    
        readsPerFile = 5000000 //* @input @description:"The number of reads per file"
        params_tophat = "-N 4" //* @input @description:"Tophat parameters" @tooltip:"parameters for Tophat2 version 2.6"
    
    .. image:: dolphinnext_images/process_input.png
	:align: center
    
|

    2) **@textbox:** It creates multi-line text field. Example usage and created form field in run page::
    
        Adapter_Sequence = "" //* @textbox @description:"You can enter a single sequence or multiple sequences in different lines." 
    
    .. image:: dolphinnext_images/process_textbox.png
	:align: center

|
    
    3) **@checkbox:** It creates checkbox for the user and their available options are defined as ``true`` or ``false`` by default. Example usage and created form field in run page::
    
        run_rRNA_Mapping = "false" //* @checkbox @description:"Check the box to activate rRNA mapping."
        rRNA_filtering = "true" //* @checkbox @description:"Check the box to filter rRNA reads."
    
    .. image:: dolphinnext_images/process_checkbox.png
	:align: center

|
    
    4) **@dropdown:** It create dropdown menu and their options can be specified by entering ``@options`` feature. Example usage and created form field in run page::
    
        genomeType = "" //* @dropdown @description:"Genome type for pipeline" @options:"hg19","mm10", "custom"
        
    .. image:: dolphinnext_images/process_dropdown.png
	:align: center

|
    
* **@description:** You can describe inputs by using ``@description`` tag. Please check the examples at above.
        
* **@tooltip:** You can also create tooltip to add detailed explanation by using ``@tooltip`` tag. See the example at below::

    params_tophat = "-N 4" //* @input @tooltip:"parameters for Tophat2 version 2.6" @description:"Tophat parameters"

* **@title:** You can also create header on top of the variable by using ``@title`` tag. This way you can easily organize the complicated form structures. See the example at below::

    params_tophat = "-N 4" //* @input @title:"Alignment Section" @description:"Tophat parameters"

* **@options:** When you define @dropdown as a formType, you should define available options by using ``@options`` tag. Please check the example dropdown at above.

Styles for Process Options
==========================

You might use additional tags to give specific shapes to form fields: A. ``@multicolumn`` B. ``@array`` C. ``condition``. 

**A. @multicolumn:**

Example::

    var1 = "" //* @input @description:"description of var1"
    var2 = "" //* @input @description:"description of var2"
    var3 = "" //* @input @description:"description of var3"
    var4 = "" //* @input @description:"description of var4"
    var5 = "" //* @input @description:"description of var5"
    var6 = "" //* @input @description:"description of var6"
    //* @style @multicolumn:{var1, var2, var3}, {var5, var6}

In this example, var1, var2 and var3 will be located in the same row, by default var4 will fill single row and, var5 and var6 will share same row as shown at below.

.. image:: dolphinnext_images/process_multicolumn.png
	:align: center
	:width: 85%

**B. @array:**

Example::

    var1 = "" //* @input @description:"description of var1" @title:"Step 1"
    var2 = "" //* @input @description:"description of var2"
    var3 = "" //* @input @description:"description of var3"
    var4 = "" //* @input @description:"description of var4" @title:"Step 2"
    //* @style @array:{var1, var2}, {var4} 
    
In this example, var1, var2 are grouped together and linked to add/remove buttons. When add button is clicked new var1, var2 fields will be created just below var1 and var2. Similarly remove button will remove generated copies of form fields. Similar features exist for just var4 variable. Please see the image at below.

.. image:: dolphinnext_images/process_array.png
	:align: center
	:width: 85%


.. tip::
    
    You can combine multiple style options on same variables. For example ``//* @style @array:{var1, var2}, {var4} @multicolumn:{var1, var2}`` will combine both multicolumn and array features for ``var1`` and ``var2`` as shown below.
    
.. image:: dolphinnext_images/process_array_multi.png
    :align: center
    :width: 85%
    

**C. @condition:**

Example::

    var1 = "" //* @dropdown @description:"description of var1" @options:"yes", "no" @title:"Step 1"
    var2 = "" //* @input @description:"description of var2"
    var3 = "" //* @input @description:"description of var3"
    var4 = "" //* @input @description:"description of var4"
    var5 = "" //* @input @description:"description of var5" @title:"Step 2"
    //* @style @condition:{var1="yes", var2}, {var1="no", var3, var4}
    
In this example, var1 value is binded to other form fields. When var1 is selected as "yes", field of var2 will be shown. Whereas when var1 is changed to "no", then var2 field will disappear and var3 and var4 fields will appear. Since var5 is not defined in @condition tag, it will be always seen while changes happening in other fields. Please see the example at below:

.. image:: dolphinnext_images/process_condi.png
	:align: center
	:width: 75%

.. tip::
    
    Similar to previous tip, you can combine all style options on same variable. For example ``//* @style @condition:{var1="yes", var2}, {var1="no", var3, var4} @array:{var1, var2, var3, var4} @multicolumn:{var1, var2, var3, var4}`` will combine features as shown below:
    
.. image:: dolphinnext_images/process_array_multi_condi.png
    :align: center
    :width: 85%
       
Autofill Feature for Process
============================
You might define hostname specific executor properties and create autofill feature by using following syntax::

    //* autofill
    if ($HOSTNAME == "ghpcc06.umassrc.org"){
    <executor properties>
    }
    //*

Here, ``$HOSTNAME`` is DolphinNext specific variable that recalls the hostname which is going to be run. Therefore, in this example, all ``<executor properties>`` will be active in case of pipeline is going to run on **ghpcc06.umassrc.org**.

**Executor Properties:**

Five type of executor properties are available to autofill **Executor Settings for Each Process**: ``$TIME``, ``$CPU``, ``$MEMORY``, ``$QUEUE``, ``$EXEC_OPTIONS`` which defines Time, CPU, Memory, Queue and Other Options. See the example below::
    
    //* autofill
    if ($HOSTNAME == "ghpcc06.umassrc.org"){
        $TIME = 3000
        $CPU  = 4
        $MEMORY = 100
        $QUEUE = "long"
        $EXEC_OPTIONS = '-E "file /home/garberlab"'
    }
    //*

.. image:: dolphinnext_images/process_autofill.png
	:align: center
	:width: 99%


Permissions, Groups and Publish
===============================

By default, all new processes are only seen by the user that created them. You can share your process with your group by selecting permissions to "Only my groups". If you want to make it public, you can change Publish option to 'Yes'. After verification of the process, process will be publish to everyone.

.. image:: dolphinnext_images/process_permpublishgroup.png
	:align: center


Copying and Revisions
=====================

It is always allowed to create a copy of your process by clicking "Settings" button *at the right top of the process window* and selecting "Copy Process". When your process is become public or it has been used by other group members, it is not allow to make changes on same revision. Instead, new revision of the process is created and changes could be done on this revision.

.. image:: dolphinnext_images/process_settings.png
	:align: center
	:width: 25%


