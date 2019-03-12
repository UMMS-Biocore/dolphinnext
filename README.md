# dolphinNext Documentation
(For readthedocs documentation https://dolphinnext.readthedocs.io)

[![Build Status](https://api.travis-ci.com/UMMS-Biocore/dolphinnext.svg?branch=master)](https://api.travis-ci.com/UMMS-Biocore/dolphinnext)

Commercially supported: UMMS Biocore, provides support for installations as well as commercial support for DolphinNext. Please contact support@dolphinnext.com

## Release 1.

## Alper Kucukural, Onur Yukselen

**Aug 29, 2018**

## Contents

- 1 DolphinNext
   - 1.1 Quick Start Guide
   - 1.2 Process Guide
   - 1.3 Pipeline Guide
   - 1.4 Project Guide
   - 1.5 Run Guide
   - 1.6 Profile Guide
   - 1.7 Amazon Cloud Guide
   - 1.8 Pipeline Examples
- 2 Indices and tables


**ii**


Contents:

**Contents 1**


**2 Contents**


# CHAPTER 1

## DolphinNext

Contents:

### 1.1 Quick Start Guide

This guide will walk you through how to start using dolphinNext pipelines and creating new pipelines.

**1.1.1 Getting Started**

First off, if you haven't installed your own mirror. You need to enter dolphinNext web page: https://dolphinnext.umassmed.edu/ and clickSign in with Google button on the top left. You will be asked to choose your google account to enter your profile.

**1.1.2 Creating Profile**

To run existing pipelines, you need to createrun environmentinprofilesection. To do so, click on the profile button
at the top right of the screen, then you’ll notice several tabs as shown below.

Follow through the steps in profile page to create SSH Key (optionally, Amazon Key) and then specify the hosts you
want to use inrun environmentssection. Once you complete these steps, you’re now able to submit jobs to specifed
hosts.


Note: Before creating run environment,SSH Keysneeded to be created in SSH Keys tab. If you want to create
Amazon EC2 instances and submit your jobs to Amazon Cloud, then bothSSHandAmazon Keyare required before
specifying run environment.

**1.1.3 Running Pipelines**

The easiest way to run pipeline is going to main page by clicking theBiocore DolphinNextbutton at the top left of
the screen. Now, you can investigate publicly available pipelines as shown at below and select the pipeline you want
run by clickingLearn Morebutton.

Once pipeline is loaded, you will notice “Run” button at the right top of the page.

This button opens new window where you can create new project by clicking “Create a Project” button. After entering
and saving the name of the project, it will be added to your project list. Now you can select your project by clicking
on the project as shown in the figure below.

**4 Chapter 1. DolphinNext**


You may proceed with entering run name which will be added to your run list of the project. Clicking “Save run” will
redirect to “run page” where you can initiate your run.

Run Page

In the header of the run page, you will notice the rocket icon and the title of the run.

Run status is monitored at the right part of the header. Initially, orangeWaitingbutton is shown. In order to initiate
run, following data need to be entered:

1. Work Directory:Full path of the directory, where nextflow runs will be executed.
2. Run Environment:Profile that is created in the profile page. If Amazon profile is selected, then
    status of the profile should to be at the stage ofrunning.
3. Inputs:Value and path of the files need to be entered.

```
Once all requirements are satisfied,Waitingbutton will turn in to greenready to runbutton as
shown below.
```
You can initiate your run by clickingready to runbutton. Please go through run page for detailed explanation
about each module is used.

**1.1. Quick Start Guide 5**


**1.1.4 Editing Processes/Pipelines**

The simpliest way to edit pipelines/processes is creating copy of the existing ones by clickingcopy pipeline/copy
processbuttons on the top right of the pages.

Once you copied the pipeline/process, you can easily modify and save on the your versions.

**1.1.5 Creating New Pipelines/Processes**

Please follow through the Pipeline Guide to create new pipelines and Process Guide for processes.

### 1.2 Process Guide

This guide will walk you through the creation of DolphinNext process.

**1.2.1 Process Window**

Once logged in, click on the pipeline tab in the top left of the screen. You’ll notice several button at the left sidebar
menu. New processes are created by clicking green “New process” button.

**1.2.2 Basics**

Once you clicked green “New process” button, new window will appear to define process components.

- Name:Process name is entered in this block. After creation of process, this name will appear in the left sidebar
    menu under the selectedmenu group.
- Description:An explanation of how process works is described in this region.
- Menu group:Selection of menu group which categorizes the processes in the left sidebar menu. If it is required,
    you may add new menu groups by clicking “add menu group” button. Afterwards, you may edit or delete these
    new groups by “edit menu group” or “delete menu group” buttons.

**1.2.3 Parameters**

This section is used to create parameters which will be used while defininginputsoroutputs. New parameters are
created by clicking “add parameter” button.

**6 Chapter 1. DolphinNext**


- Identifier:Identifier is simply parameter name and allows you to call same parameters in other processes.

Note: When qualifier set toval,identifieris used to filter available nodes while connecting each nodes.
However, whenfileorsetis selected as qualifier,file typeis used for filtering available nodes.

- Qualifier: Three main type of qualifiers ( **file** , **set** and **val** ) are exist in DolphinNext:
    1.File:Uses the received value as a file. Example usage in nextflow file:

```
file genome
```
```
2.Set:Allows to handle a group of input values having other qualifiers. Example usage:
```
```
set val(name), file(genome)
```
```
3.Val:Allows to access the received input value by its name in the process script. Example usage:
```
```
val script_path
```
- File type:If qualifier is set tofileorset, file type option will appear. This option will be used to filter
    available nodes while generating pipelines.

Tip: For instance, you may creategenomeparameter by entering identifier as:genome, qualifier:fileand file
type:fasta. Similarly for creatingscript_pathparameter you can define identifier as:script_pathand
qualifier:val.

**1.2.4 Inputs**

This section where you enter all of your input nodes of the process. You can start adding by clicking “Add input... ”
dropdown. After adding selected parameter as a input node, “input name” box and “add operator” button will appear.

**1.2. Process Guide 7**


Input name box used to define nextflow variables which will be used in the scripts section. For instance, if you enter
input name asgenome, in the scripts section you can recall this variable as${genome}. Other examples are listed
in the following table:

```
Qualifier Input name Recall in the Script
val script_path ${script_path}
file genome ${genome}
set val(name), file(genome) ${genome}
set val(name), file(genome) ${name}
```
Additionally, if you need to transform values emitted by a channel, you can click “operators” button and select opera-
tors from dropdown. For detailed information, you can continue to read operators section.

**1.2.5 Outputs**

The output nodes are defined in this section. Similar to adding inputs, by clicking “Add output... ” dropdown and
selecting output parameter will showoutput namebox,add operatorandadd regular expressionbuttons.

Output files, created by the process, are grabbed by “output name” box. By entering the pattern of the output files eg.
genome.index*would grab the files that are starts withgenome.index. Besides you can use nextflow variables
which are defined in inputs or scripts section. As an example, if you enter input name asgenome, in the outputs
section you can recall this variable as'${genome}_out.txt'. Other examples are listed in the following table:

```
Input Qualifier Input name Output Qualifier Output name
file genome file “${genome}_out.txt”
set val(name), file(reads) set val(name), file(“${name}.unmap*.fq”)
set val(name), file(reads) file “${name}.bam”
set val(name), file(genome) file “genome.index*”
```
Additionally, if you need to transform values emitted by a channel, you can click “operators” button and select oper-
ators from dropdown. For detailed information, you can continue to read operators section. In addition to operators,
regular expressions can be activated by clicking “regular expressions” button. You can learn more about this section
by clicking regular expressions section.

**1.2.6 Scripts**

Main process scripts are defined in this region. Three type of mode are available in Nextflow: A. Script B. Shell C.
Exec. These statements defines how the command going to be executed by the process.

A. Script:

For simplicity, DolphinNext uses script format by default, and accepts each line as a command. It is same as using
three double quotes"""at the start and the end of the command block. If you use three double quotes, DolphinNext
will take that particular area as command block. Therefore, following two strings will be executed as same:

**8 Chapter 1. DolphinNext**


""" tophat2 -o. ${indexPath} $reads """

tophat2 -o. ${indexPath} $reads

Each line is executed as a BASH script in the host. It can be any command or script that is typically used in terminal
shell or BASH script.

- Conditional Scripts:

Conditional scripts can be used with control statements such as if, switch etc. In order to use these feature, you must
start the code block by usingscript:keyword. In this way, the interpreter will evaluate all the following statements
as a code block and find the script string to be executed. Please check the following example:

script:
name = reads.toString() - '.fastq'

**if** (mate == "pair") {
"""
bowtie2 -x genome.index -1 ${reads.join(' -2 ')} -S ${name}_alignment.sam --un-
˓→conc ${name}_unalignedreads
"""
}
**else if** (mate == "single") {
"""
bowtie2 -x genome.index -U $reads -S ${name}_alignment.sam --un ${name}_
˓→unalignedreads
"""
}

Tip: As shown in the example above, it is possible to run Bowtie based on mate status of the reads. In order to
activate this property, you must addmateparameter as an input. While you are running the pipeline, you may select
singleorpairoption according to your needs.

B. Shell:

**1.2. Process Guide 9**


Alternatively, you can useshellblock where Nextflow variables are declared by exclamation mark!. This feature
allows you to use both Nextflow and BASH variables in the same code without using escape character. In order to
use shell mode, you start code by addingshell:and add three single quotes (''') at the start and the end of the
command block:

shell:
'''
echo $PATH and !{new_path}
'''

C. Exec:

Nextflow processes allows you to execute native code other than system commands. This way you may easily follow
local Nextflow variables. To start exec mode, you can addexec:block at the beggining of the script:

exec:
println "${genome}"

**1.2.7 Operators**

If you need to transform values emitted by a channel, you can click “operators” button and select operators from
dropdown. Optionally, operator content could be specified to adjust their act. Besides, multiple operators can be
added by starting paranthesis(. Examples are listed in the following table:

```
Operator Operator Con-
tent
```
```
Usage
```
```
flatMap ().buffer(size:3) Groups 3 emitted item into one new channel.
mode flat-
ten
```
```
To emit each output file as a sole item
```
```
groupTuple Collects tuples (or lists) of values emitted and groups them based on their key
value.
```
Tip: To get more information about operators, you can use this link to reach Nextflow documentation.

**1.2.8 Regular Expressions**

This is optional regular expresion feature to filter output files. By default DolphinNext usesoutput namebox pattern
to decide which files are going to be transferred to output directory. If this feature is not sufficient or additional
filtration is required, then regular expression feaure might be activated by clicking “Regular Expressions” button at the
outputs section. Example usage:

**1.2.9 Header Script**

This section allows you to add additional scripts or comments before process block starts. This way you may recall
same function several times in the script section.

**10 Chapter 1. DolphinNext**


**1.2.10 Process Options**

You may separate your main process inputs and optional parameters by usingProcess Optionsfeature. On the run
page, these parameters will be asked separately in the Process Options section as in the image shown at below:

In order to create these form, you need to use following syntax in theprocess header:

variableName = defaultValue //* **@formType @description** :"..." **@tooltip** :"..." **@options** :
˓→"..."

Note: You can define defaultValue with single/double quotes (for strings) or without any quotes (for numbers).

- @formType: Four type of form fields are available in DolphinNext (@input,@textbox,@checkbox,
    @dropdown):
       1.@input:It creates single-line text field. Example usage and created form field in run page:

```
readsPerFile = 5000000 //* @input @description :"The number of reads
˓→per file"
params_tophat = "-N 4" //* @input @description :"Tophat parameters"
˓→ @tooltip :"parameters for Tophat2 version 2.6"
```
**1.2. Process Guide 11**


2. @textbox:It creates multi-line text field. Example usage and created form field in run page:

```
Adapter_Sequence = "" //* @textbox @description :"You can enter a single
˓→sequence or multiple sequences in different lines."
```
3. @checkbox:It creates checkbox for the user and their available options are defined astrueor
    falseby default. Example usage and created form field in run page:

```
run_rRNA_Mapping = "false" //* @checkbox @description :"Check the box to
˓→activate rRNA mapping."
rRNA_filtering = "true" //* @checkbox @description :"Check the box to
˓→filter rRNA reads."
```
4. @dropdown:It create dropdown menu and their options can be specified by entering@options
    feature. Example usage and created form field in run page:

```
genomeType = "" //* @dropdown @description :"Genome type for pipeline"
˓→ @options :"hg19","mm10", "custom"
```
**12 Chapter 1. DolphinNext**


- @description:You can describe inputs by using@descriptiontag. Please check the examples at above.
- @tooltip:You can also create tooltip to add detailed explanation by using@tooltiptag. See the example at
    below:

```
params_tophat = "-N 4" //* @input @tooltip :"parameters for Tophat2 version 2.6"
˓→ @description :"Tophat parameters"
```
- @title: You can also create header on top of the variable by using@titletag. This way you can easily
    organize the complicated form structures. See the example at below:

```
params_tophat = "-N 4" //* @input @title :"Alignment Section" @description :"Tophat
˓→parameters"
```
- @options: When you define @dropdown as a formType, you should define available options by using
    @optionstag. Please check the example dropdown at above.

**1.2.11 Styles for Process Options**

You might use additional tags to give specific shapes to form fields: A.@multicolumnB.@arrayC.condition.

A. @multicolumn:

Example:

var1 = "" //* **@input @description** :"description of var1"
var2 = "" //* **@input @description** :"description of var2"
var3 = "" //* **@input @description** :"description of var3"
var4 = "" //* **@input @description** :"description of var4"
var5 = "" //* **@input @description** :"description of var5"
var6 = "" //* **@input @description** :"description of var6"
//* **@style @multicolumn** :{var1, var2, var3}, {var5, var6}

In this example, var1, var2 and var3 will be located in the same row, by default var4 will fill single row and, var5 and
var6 will share same row as shown at below.

**1.2. Process Guide 13**


B. @array:

Example:

var1 = "" //* **@input @description** :"description of var1" **@title** :"Step 1"
var2 = "" //* **@input @description** :"description of var2"
var3 = "" //* **@input @description** :"description of var3"
var4 = "" //* **@input @description** :"description of var4" **@title** :"Step 2"
//* **@style @array** :{var1, var2}, {var4}

In this example, var1, var2 are grouped together and linked to add/remove buttons. When add button is clicked new
var1, var2 fields will be created just below var1 and var2. Similarly remove button will remove generated copies of
form fields. Similar features exist for just var4 variable. Please see the image at below.

**14 Chapter 1. DolphinNext**


Tip: You can combine multiple style options on same variables. For example//* @style @array:{var1,
var2}, {var4} @multicolumn:{var1, var2}will combine both multicolumn and array features for
var1andvar2as shown below.

**1.2. Process Guide 15**


C. @condition:

Example:

var1 = "" //* **@dropdown @description** :"description of var1" **@options** :"yes", "no"
˓→ **@title** :"Step 1"
var2 = "" //* **@input @description** :"description of var2"
var3 = "" //* **@input @description** :"description of var3"
var4 = "" //* **@input @description** :"description of var4"
var5 = "" //* **@input @description** :"description of var5" **@title** :"Step 2"
//* **@style @condition** :{var1="yes", var2}, {var1="no", var3, var4}

In this example, var1 value is binded to other form fields. When var1 is selected as “yes”, field of var2 will be shown.
Whereas when var1 is changed to “no”, then var2 field will disappear and var3 and var4 fields will appear. Since
var5 is not defined in @condition tag, it will be always seen while changes happening in other fields. Please see the
example at below:

**16 Chapter 1. DolphinNext**


Tip: Similar to previous tip, you can combine all style options on same variable. For example//* @style
@condition:{var1="yes", var2}, {var1="no", var3, var4} @array:{var1, var2,
var3, var4} @multicolumn:{var1, var2, var3, var4}will combine features as shown below:

**1.2. Process Guide 17**


**1.2.12 Autofill Feature for Process**

You might define hostname specific executor properties and create autofill feature by using following syntax:

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
<executor properties>
}
//*

Here,$HOSTNAMEis DolphinNext specific variable that recalls the hostname which is going to be run. There-
fore, in this example, all<executor properties>will be active in case of pipeline is going to run ongh-
pcc06.umassrc.org.

Executor Properties:

Five type of executor properties are available to autofillExecutor Settings for Each Process: $TIME,$CPU,
$MEMORY,$QUEUE,$EXEC_OPTIONSwhich defines Time, CPU, Memory, Queue and Other Options. See the
example below:

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
$TIME = 3000
$CPU = 4
$MEMORY = 100
$QUEUE = "long"
$EXEC_OPTIONS = '-E "file /home/garberlab"'
}
//*

**1.2.13 Permissions, Groups and Publish**

By default, all new processes are only seen by the user that created them. You can share your process with your group
by selecting permissions to “Only my groups”. If you want to make it public, you can change Publish option to ‘Yes’.
After verification of the process, process will be publish to everyone.

**1.2.14 Copying and Revisions**

It is always allowed to create a copy of your process by clicking “Settings” buttonat the right top of the process window
and selecting “Copy Process”. When your process is become public or it has been used by other group members, it
is not allow to make changes on same revision. Instead, new revision of the process is created and changes could be
done on this revision.

**18 Chapter 1. DolphinNext**


### 1.3 Pipeline Guide

This guide will show how to create, edit, delete and share pipelines.

**1.3.1 Basics**

Once logged in, click on the pipeline button in the top left of the screen. You’ll noticeEnter Pipeline Namebox, just
below the Pipelines button.

After entering pipeline name, you will seeSaving..andAll changes savednotifications just to the right of the pipeline
name box and buttons. In DolphinNext, autosaving mode is active while creating pipelines for ease of use. You may
also use “Save Pipeline” button to save your pipelines.

Another way to create new pipeline is clicking orange “New pipeline” button which is located at left sidebar menu.

Note: You can create a copy of your pipeline by clicking “Copy Pipeline” button and delete your pipeline by clicking
“Delete Pipeline” button. While creating your pipeline, you can always check the created nextflow script by clicking
“Download Pipeline” button.

**1.3.2 Processes**

Processes can be reached by “Processes” section at the left sidebar menu. Under each menu group, assigned processes
are located. You can drag and drop the process name on the workplace area. Process circle will appear as shown in
the figure below:

You will notice several icons on the process circle:

**1.3. Pipeline Guide 19**


- Settings icon, located at the top, opens “Select Process Revision” window where you may examine the
    current state of process or you may jump to the other revisions and replace the selected revisions on the
    workplace.
- Pencil iconwhich is at the center of the circle, allow you to edit process name which will be placed in the
    nextflow file as a process name. Therefore process names should be unique in overall pipeline.
- At the bottom of the process circle,bin iconis located to delete current process from the pipeline.

**1.3.3 Input Parameters**

Input files or values which will be used in the process, need to be added byInput parameterscircle.Input param-
etersare located on the top of the Processes section at the left sidebar menu. Similar to adding processes, drag and
drop feature is available forInput parameters. When dragging is completed, orange and smaller circle will appear in
the workplace as shown in the figure below:

By using thePencil iconwhich is located at the left side of the circle, you can edit name of the input parameters.
These names will be used while creating variables in the nextflow file, therefore these names should be unique in
overall pipeline.

**1.3.4 Output Parameters**

Newly created files that are defined in the Outputs section are selected byOutput parameterscircle.Output param-
etersare located just below of theInput parametersat the left sidebar menu and placed into workspace by dragging.
The name of the circle determines the name of the published directory which can be edited by clickingpencil icon. In
the example below, both aligned and unaligned reads are selected by two separate green circles. Both of outputs will
be transferred to separate directories called aligned and unaligned.

**20 Chapter 1. DolphinNext**


Tip:

- If you don’t want to keep some of your output files, you may leave output nodes unconnected. Then only files
    that are selected will be transferred in the published directory.
- In order to transfer two or more outputs into same directory, you can use the same name forOutput parameters.

**1.3.5 Edges**

There are three main reasons for creating edges.

1. To establish a connection between inputs and outputs of the multiple processes
2. To define input files or values which will be used in the processes
3. To select outputs that will be published in the output directory.

This procedure is facilitated by showing available nodes which are ready for connection. The figure below shows
an example about this feature: Since mouse is over the output node: genome_indexof theBuild_indexand
DolphinNext shows the available node on theMap_tophat2process by marking.

Important: This filtering is executed by checkingfile typesof the set and file parameters andnameof the val
parameters. In this example, file type of the selected output node and the marked input node bothindex.

**1.3. Pipeline Guide 21**


This feature is also available while connecting input parameters to multiple processes.

**1.3.6 Pipeline Header Script**

This section allows you to add additional inputs, scripts or comments before pipeline starts. This way you may recall
same function several times in the other processes.

**1.3.7 Adding Pipeline Inputs by Pipeline Header Script**

You can usepipeline header scriptto add pipeline inputs which is very much similar to adding dropdown for process
options. In order to create inputs, you need to use following syntax in thepipeline header script:

params.variableName = defaultValue //* **@dropdown @options** :"..."

Note: Please note that you need to useparams.at the beginning ofvariableName. You can define defaultValue
with single/double quotes (for strings) or without any quotes (for numbers).

On the run page, these input parameters will be asked separately in theInputssection as in the image shown at below:

**22 Chapter 1. DolphinNext**


params.genome_build = "" //* **@dropdown @options** :"human_hg19, mouse_mm10, custom"
params.run_Tophat = "no" //* **@dropdown @options** :"yes","no"

**1.3.8 Autofill Feature for Pipeline Inputs**

A.Based on Hostname:

DolphinNext allows you to enter hostname specific input parameters by using following syntax:

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
<input parameters>
}
//*

Here,$HOSTNAMEis DolphinNext specific variable that recalls the hostname which is going to be run. Therefore,
in this example,<input parameters>will be filled in case of pipeline is going to run onghpcc06.umassrc.org.
Please check following example in which TrimPath parameter is filled automatically:

//* autofill
if ($HOSTNAME == "garberwiki.umassmed.edu"){
params.TrimPath ="/project/Trimmomatic/trimmomatic-0.32.jar"
}
//*

**1.3. Pipeline Guide 23**


B.Based on Selected Input:

It is possible autofill based on the selected parameters on the running page. In the following example, path of the
program will be changed according to selected pipeline parameter:method:

params.method = "" //* **@dropdown @options** :"trimmomatic, fastqx"
//* autofill
**if** (params.method == "trimmomatic"){
params.TrimPath ="/project/Trimmomatic/trimmomatic-0.32.jar"
}
**else if** (params.method == "fastqx"){
params.TrimPath ="/project/fastqx/fastqx"
}
//*

**24 Chapter 1. DolphinNext**


C.Dynamic Autofill:

In order to autofill parameters that have kind of pattern, you can use dynamic autofill feature. To do so, you need
to define variable parts of the pattern by using underscore such as_speciesor_build. Afterwards, you can
define these variable based on if conditions. Finally, you can activate the autofill feature by checking the existance by
following syntax:

if (params.variableName && $HOSTNAME){
<input parameters>
}

or

if ($HOSTNAME){
<input parameters>
}

Please check the example below whereparams.genomeandparams.genomeIndexPathfilled according to
selected parameters ofparams.genome_buildand$HOSTNAME:

params.genome_build = "" //* @dropdown @options:"human_hg19, mouse_mm10"
def _species;
def _build;
(continues on next page)

**1.3. Pipeline Guide 25**


```
(continued from previous page)
```
def _share;
//* autofill
if (params.genome_build == "human_hg19"){
_species = "human"
_build = "hg19"
} else if (params.genome_build == "mouse_mm10"){
_species = "mouse"
_build = "mm10"
}

if ($HOSTNAME == "garberwiki.umassmed.edu"){
_share = "/share/dolphin_data/genome_data"
} else if ($HOSTNAME == "ghpcc06.umassrc.org"){
_share = "/project/data/genome_data"
}
if (params.genome_build && $HOSTNAME){
params.genome ="${_share}/${_species}/${_build}/${_build}.fa"
params.genomeIndexPath ="${_share}/${_species}/${_build}/${_build}"
}
if ($HOSTNAME){
params.TrimPath ="${_share}/Trimmomatic/trimmomatic-0.32.jar"
}
//*

**26 Chapter 1. DolphinNext**


**1.3.9 Autofill Feature for Pipeline Properties**

You might define hostname specific executor properties and create autofill feature by using following syntax:

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
<executor properties>
}
//*

Here,$HOSTNAMEis DolphinNext specific variable that recalls the hostname which is going to be run. There-
fore, in this example, all<executor properties>will be active in case of pipeline is going to run ongh-
pcc06.umassrc.org.

Executor Properties:

Five type of executor properties are available to autofillExecutor Settings for All Processes: $TIME,$CPU,

**1.3. Pipeline Guide 27**


$MEMORY,$QUEUE,$EXEC_OPTIONSwhich defines Time, CPU, Memory, Queue and Other Options. See the
example below:

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
$TIME = 3000
$CPU = 4
$MEMORY = 100
$QUEUE = "long"
$EXEC_OPTIONS = '-E "file /home/garberlab"'
}
//*

Singilarity/Docker Images:

Four type of image properties are available to autofill : $DOCKER_IMAGE, $DOCKER_OPTIONS,
$SINGULARITY_IMAGE, $SINGULARITY_OPTIONS which automatically fills the image path and
RunOptionsfields of docker and singularity. See the example below for docker:

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
$DOCKER_IMAGE = "docker://UMMS-Biocore/docker"
$DOCKER_OPTIONS = "-v /export:/export"
}
//*

Singularity image example:

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
$SINGULARITY_IMAGE = "shub://UMMS-Biocore/singularity"
$SINGULARITY_OPTIONS = "--bind /project"
}
//*

**28 Chapter 1. DolphinNext**


**1.3.10 Pipeline Details**

This section summarizes all used processes and input/output parameters to give you an overall view about pipeline.

**1.3.11 Permissions, Groups and Publish**

By default, all new pipelines are only seen by the owner. You can share your pipeline with your group by selecting
permissions to “Only my groups”. If you want to make it public, you can change Publish option to ‘Yes’. After
verification of the pipeline, it will be publish to everyone.

**1.3.12 Copying and Revisions**

It is always allowed to create a copy of your pipele by clicking “Copy Pipeline” button just to the right of the pipeline
name box and buttons. When your pipeline is set to public or it has been used by other group members, it is not allow
to make changes on same revision. Instead, new revision of the pipeline can be created and changes could be done on
this revision.

### 1.4 Project Guide

This guide shows how to create project and how to insert pipeline and file into it.

**1.4.1 Creating Projects and Adding Pipelines**

Projects are platforms to categorize all of your runs and files. To run a pipeline, it is required to add selected pipelines
into your project. There are two ways to create a new project and add pipelines to it:

**1.4. Project Guide 29**


- 1. First, click the “Projects” button in the top of the screen. In this new page, new projects can be
    inserted by clicking “Create a Project” button. After saving the name of the project, it will be
    added to your projects table as shown in the figure below:

```
Note: You can always edit or remove your projects by clicking “edit” or “remove” button on the project
table.
```
```
Now you are able to enter the “project page” by clicking the “name of the project” at the projects table. You will notice three sections in the project page:
```
- Description:Box to keep project related information.
- Project Runs:Table to add your pipelines as a run by clicking “Add Pipeline to Run” button.
    You may enter your run page by clicking “name of the run”.
- Project Files:Table for adding your project files. It allows you to reuse them in the multiple
    runs.
- 2. Alternatively you may click the “Pipelines” button in the top left of the screen. Then select
the pipeline you want run from left sidebar menu. At the right top of the page, you will notice
“Run” button.

```
This button opens new window where you can create new project by clicking “Create a Project” button.
After entering and saving the name of the project, it will be added to your project list. Now you can select
your project by clicking on the project as shown in the figure below.
```
**30 Chapter 1. DolphinNext**


```
You may proceed with entering run name which will be added to your run list of the project. Clicking
“Save run” will redirect to “run page” where you can initiate your run.
```
### 1.5 Run Guide

In the previous section (project guide), we saw the creation of project and adding pipelines to it. In this section, we
will investigate the run settings to initiate our new run.

**1.5.1 Basics**

In the header of the run page, you will notice the rocket icon and the title of the run where you can also edit your
run name. Tracking of your run is facilitated by project and pipeline links which are located just next to run name as
shown at below:

Similar to pipeline section,Save,Download Pipeline,Copy RunandDelete Runicons are found in the header
section to manage your run. Besides, optionalRun Descriptionsection is exist just below the header section.

**1.5.2 Run Status**

Run status is monitored at the right part of the header. Initially, orangeWaitingbutton is shown. In order to initiate
run, following data need to be entered:

1. Work Directory:Full path of the directory, where nextflow runs will be executed.
2. Run Environment:Profile that is created in the profile page. If Amazon profile is selected, then status of the
    profile should to be at the stage ofrunning.
3. Inputs:Value and path of the files need to be entered.

```
Warning: If amazon s3 path is entered as a input or publish directory path, amazon keys (which will appear in
theRun Settingsection) need to be also selected.
```
All available status messages are listed at table below:

**1.5. Run Guide 31**


```
Status Meaning
Waiting Waiting for inputs, output directory and selection of active environment (*amazon keys, if s3 path is
used)
Ready Ready to initiate run
Connect-
ing
```
```
Sending SSH queries to selected host system
```
```
Waits Job is submitted, waits for the execution
Running Nextflow is executed and running the jobs.
Completed Nextflow job is completed.
Run Error Error occured before submiting the jobs or while executing the jobs.
Termi-
nated
```
```
User terminated the run by using “terminate run” button.
```
**1.5.3 Run Settings**

- Work Directory:Full path of the directory, where all nextflow runs will be executed. Example paths:

```
~/workdir
/home/newuser/workdir
```
- Run Environment:Profile that is created in the profile page. If Amazon profile is selected, then status of the
    profile should to be at the stage ofrunning.
- Use Docker Image:Nextflow supports the Docker containers which allows you to create fully reproducible
    pipelines. Docker image can contain any kind of software that you might need to execute your pipeline. It
    works transparently and output files are created in the host system without requiring any addition step. The only
    requirement is the installation of the Docker on the execution platform. To activate this feature in DolphinNext
    just click the “Use Docker Image” checkbox and enter following information:
       1. Image:Docker image name. Example:

```
nephantes/dolphinnext-docker
```
2. RunOptions (optional):You can enter any command line options supported by the docker run command.
    Please click this link for details.
- Use Singularity Image:Alternative to Docker, you can activate singularity image by clicking “Use Singularity
Image” checkbox and entering relevant fields. The only requirement is the installation of the Singularity on the
execution platform.
1.Image:Path to sigularity image. Example:

```
project/umw_biocore/singularity/UMMS-Biocore-singularity-master.simg
```
```
2.RunOptions (optional): You can enter any command line options supported by the
singularity exec. Please click link for details. For instance, you can mount the direc-
tories by using--bind command. Example:
```
```
--bind /project:/project --bind /nl:/nl --bind /share:/share
```
```
Tip: Mounting directories in singularity requires you to create the directories in the image before-
hand.
```
**32 Chapter 1. DolphinNext**


**1.5.4 Advanced Options**

- Run Command (optional):You may run the command or commands (by seperating each command with&&
    sign) before the nextflow job starts. eg:

```
source /etc/bashrc && module load java/1.8.0_31 && module load bowtie2/2.3.2
```
- Publish Directory:Work directory is default publish directory for DolphinNext. If you want to enter new
    publish directory, just click this item and enter the full path of publish directory. Both local paths (eg./home/
    user/test) or amazon s3 paths (eg.s3://yourbucket/test) are accepted.
- Executor Settings for All Processes:If any option other than local and ignite is selected asnextflow executor
    in the profile, it is allowed to override and adjust these settings by clicking this item. Following settings will be
    prompted:Queue,Memory(GB),CPUandTime(min.).

```
Note: In case of non-standart resources or settings is required for executor, then you can specify
these parameters by usingOther optionsbox. For instance, to submit SGE job with 3 CPU by using
paralel environments, you may enter-pe orte 3(to use MPI for distributed-memory machines)
or-pe smp 3(to use OpenMP for shared-memory machines) in theOther optionsbox andjust
leave the CPU box empty!
```
- Executor Settings for Each Process: You may change executor settings for each process and override to
    executor settings for all processesby clicking this item and clicking the checkbox of process that you want to
    change. This will only affect the settings of clicked process and keep the original settings for the rest. Similarly,
    following settings will be prompted for checked process:Queue,Memory(GB),CPUandTime(min.).
- Delete intermadiate files after run:This is default settings for DolphinNext to keep only selected output files
    in the work/publish directory and removing the rest of the files. Here the main goal is to minimize the required
    space for each project.
- Permissions and Groups:By default, all new runs are only seen by the owner. However, you can share your
    run with your group by changing permissions to “Only my groups” and choose the group you want to share
    fromgroup selectiondropdown.

**1.5.5 Pipeline Files**

This section is separated into two groups:inputsandoutputs.

- Inputs:The input file paths or values are entered by clickingSelect FileorEnter Valuebutton. In order to
    selectmultiple files, wildcard characters*,?,[]and{}should be used. These arguments are interpreted
    as a glob path matcher by Nextflow and returns a list of paths that are matching the specified pattern. Several
    examples to define inputs are listed below:

```
Input Type Example
File/Set /share/data/mm10.fa
File/Set /share/validfastq/*_{1,2}.fastq
Val pair
Val ~/scripts/filter.py
```
- Outputs:When the run successfully completes, the path of the output files will be appeared in this region.

**1.5. Run Guide 33**


**1.5.6 Workflow**

To give you an overview, overall pipeline and its description are showed in this region. You may hide it by clicking
minus/plus icon just next toWorkflowtitle.

**1.5.7 Run Logs**

Log section will appear below of therun descriptionas soon as you click the “Ready to Run” button. You can monitor
each step of the run both before and after nextflow execution as shown at figure below.

If any error occured on any of these steps, detailed explanation about the error will be displayed in this section and run
error sign will appear in the right side of the header as show in the example below:

### 1.6 Profile Guide

This guide will walk you through all of your options within the Profile page.

**1.6.1 Profile Page**

Once logged in, click on the profile tab in the top right of the screen. You’ll notice several tabs to explore in profile
page.

**34 Chapter 1. DolphinNext**


First tab is therun environments. This is your main segment for creating connection profiles. Second tab isgroups
where you can create group and add members to it. Next section is theSSH Keys, where you can create new or enter
existing SSH key pairs to establish connection to any kind of host. Fourth tab is calledAmazon Keyswhere you enter
your Amazon Web Services (AWS) security credentials to start/stop Amazon EC2 instances.

Tip: Before creating run environment,SSH Keysneeded to be created in SSH Keys tab. If you want to create
Amazon EC2 instances and submit your jobs to Amazon Cloud, then both SSH and Amazon Key are required before
specifying run environment.

**1.6.2 SSH Keys**

In the SSH keys tab, you can create new or enter existing SSH key pairs by clicking on “Add SSH Key” button. By
using Add SSH Keys window, enter the name of your keys and select the method you want to proceed:A. Use
your own keysorB. Create new keys.

- A. Use your own keys:If you choose “use your own keys”, your private and public key pairs will be asked. You
    can reach your key pairs in your computer at default location:~/.ssh/id_rsafor private and~/.ssh/
    id_rsa.pubfor public key. If these files are not exist or you want to create new ones, then on the command
    line, enter:

```
ssh-keygen -t rsa
```
You will be prompted to supply a filename and a password. In order to accept the default filename (and location) for
your key pair, press Enter without entering a filename. Your SSH keys will be generated using the default filename
(id_rsaandid_rsa.pub).

- B. Create new keys:You will proceed by clicking generate keys button where new pair of ssh keys will be
    specifically produced for you. You should keep these keys in your default .ssh directory (~/.ssh/id_rsafor
    private and~/.ssh/id_rsa.pubfor public key). It is required to be adjust your public key permissions to
    644 (-rw-r–r–) and private key permissions to 600 (-rw——-) by entering following commands:

```
chmod -R 644 ~/.ssh/id_rsa
chmod -R 600 ~/.ssh/id_rsa_pub
```
**1.6. Profile Guide 35**


```
Warning: In both of the cases, private key will be used for submiting jobs in the host. Therefore, public key
required to be added into~/.ssh/authorized_keysin the host by user.
```
In order to add you private key to the host, you might use the following command:

cat ~/.ssh/id_rsa.pub | ssh USERNAME **@HOSTNAME** "mkdir -p ~/.ssh && cat >> ~/.ssh/
˓→authorized_keys"

After inserting public key, connect to the host and make sure file permissions ofauthorized_keysandsshfolder
is correct by following commands:

chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys

**1.6.3 Amazon Keys**

In the Amazon keys tab, you can enter your AWS security credentials (access key, secret key and default region) by
clicking on “Add Amazon Key” button. Your information will be encrypted and kept secure. You will only have full
access to editing and viewing the key information.

**1.6.4 Groups**

Groups tab is used to create groups by clicking on “Create a Group” button. After creating group, you can add members
by clickingOptions > Add Userbutton. By using this group information, you can share your process, pipeline
or projects with group members. In order to see current members of the group, you can clickOptions > View
Group Membersbutton. You can also delete your group by clickingOptions > Delete Groupbutton.

**36 Chapter 1. DolphinNext**


**1.6.5 Run Environments**

This section is used for defining connection profiles by clicking on “Add Environment” button. Type options (A. Host
or B. Amazon) will be prompted to user. You may choose “Host” option, if you are planing to submit jobs to specified
hosts by using SSH Keys, or you may proceed with “Amazon” option to create Amazon EC2 instances and submit
your jobs to Amazon Cloud.

**1.6.6 A. Defining Host Profile:**

- Username/Hostname:You should enter your username and hostname of the host which you would like to con-
    nect. For instance, in order to connectus2r@ghpcc06.umassrc.org, you would enterus2ras username
    andghpcc06.umassrc.orgas hostname.

Note: You don’t need to add@sign while entering this information.

- SSH Keys:are saved in SSH keys tab and will be used while connecting to host.
- Run Command (optional):You may run the command or commands (by seperating each command with&&
    sign) before the nextflow job starts. eg.source /etc/bashrc && module load java/1.8.0_31
    && module load bowtie2/2.3.2
- Nextflow Path (optional):If nextflow path is not added to$PATHenvironment, you can define the path in this
    block. eg.‘‘/project/umw_biocore/bin‘‘
- Executor of Nextflow/Executor of Nextflow Jobs:You can determine the system where nextflow itself is
    initiated. Currently local, sge and lsf executors are supported by DolphinNext to initiate nextflow. Apart from
    the executor of nextflow, you may change the executor of each job by using “Executor of Nextflow Jobs”
    option. If any option other than local and ignite, is selected, additional settings will be prompt forQueue,
    Memory(GB),CPUandTime(min.). Adjustment of these parameters are allowed for both options.

Note: For instance you may initiate nextflow inlocaland allow nextflow to run its jobslocal,sge,lsf,
slurumorignite. Alternatively, selection both options tolsforsgewould allow both executions to be main-
tained bylsforsgeexecutor.

Note: In case of non-standart resources or settings is required for executor, then you can specify these parameters by
usingOther optionsbox. For instance, to submit SGE job with 3 CPU by using paralel environments, you may enter

**1.6. Profile Guide 37**


-pe orte 3(to use MPI for distributed-memory machines) or-pe smp 3(to use OpenMP for shared-memory
machines) in theOther optionsbox andjust leave the CPU box empty!

**1.6.7 B. Defining Amazon Profile:**

- SSH Keys:are saved in SSH keys tab and will be used while connecting to host.
- Amazon Keys:AWS credentials that are saved in Amazon keys tab and will allow to start/stop Amazon EC2
    instances.
- Instance Type:Amazon EC2 instance types that comprise varying combinations of CPU, memory, storage, and
    networking capacity (eg.m3.xlarge).
- Image Id:Virtual machine ID (eg.ami-35626d4f). If you want to create your own image, it should support
    singularity, docker engine (version 1.11 or higher), Apache Ignite, Cloud-init package, and Java runtime (version
    8).
- Subnet Id/Shared Storage Id/Shared Storage Mount: The filesystem needs to be created at https://console.aws.amazon.com/efs/ and these three information will be obtained upon creation of shared file system. Make sure following criterias are satisfied:
    1. Image has the directory to mount this storage.
    2. The output directory needs to be under this mount location.
    3. The storage system needs to be created in selected region and necessary rights need to be given in the
       console.
- Run Command (optional):You may run the command or commands (by seperating each command with&&
    sign) before the nextflow job starts. eg.source /etc/bashrc && module load java/1.8.0_31
    && module load bowtie2/2.3.2
- Nextflow Path (optional):If nextflow path is not added to$PATHenvironment, you can define the path in this
    block. eg./project/umw_biocore/bin
- Executor of Nextflow/Executor of Nextflow Jobs:Amazon instances are automatically configured to use the
    Ignite executors. Therefore, while defining amazon profile, you should selectlocalforExecutor of Nextflow
    andigniteforExecutor of Nextflow Jobs.

### 1.7 Amazon Cloud Guide

DolphinNext supports submitting jobs to the Amazon AWS cloud by using Nextflow. It allows you to practically
setup, start/stop a computing cluster and run your pipeline in the AWS infrastructure.

**1.7.1 Configuration**

Once logged in, click on the profile tab in the top right of the screen. Both SSH and Amazon Keys need to be entered
in each tab. Then you can proceed with creating amazon connection profile in the run environments tab. After creating
profile “start/stop” button will appear in actions column of your amazon profile as shown in the figure below:

**38 Chapter 1. DolphinNext**


Clicking on the start/stop button will open new window calledAmazon Management Console.

**1.7.2 Amazon Management Console**

Starting and stoping AWS cloud is conducted in Amazon management console. There are two ways to open console.
First option is clicking following buttons:profile > run environments > Start/Stop. Altervatively,
you can quickly reach Amazon console by clicking “Amazon” button at the top of the screen. The number of active
profile is displayed with green tag at the top of the amazon button.

When first created, state of your profile will be inactive as shown at below:

**1.7.3 Starting Cluster**

In order to active AWS cluster, click on “start” button of the profile you wanted to initiate. Following options will be
prompted.

- Nodes:Enter the number of instances, you want to initiate. First node is created asmaster, and the remaining
    asworkers.
- Use autoscale:This is Nextflow’s critical feature which allows the cluster to adapt dynamically to the workload
    by changing computing recources. After clicking this option and entering number ofMaximum Instances,
    new instances will be automatically added to the cluster when tasks remain too long in wait status. The upper
    limit should be entered byMaximum Instancesto control the size of cluster. By default unused instances are
    removed when they are not utilised.

Profile status will be updated asWaiting for replyas soon as you click the “Activate Cluster” button. If
your credentials and profile are correct, profile status will change toInitializingandRunning, respectively.
However, in case of missing or wrong profile information, status will turn intoTerminatedand reason of the error
will appear next to the status. All available states of the profile are listed in table below:

**1.7. Amazon Cloud Guide 39**


```
Status Meaning
Inactive AWS cloud has not initiated yet.
Waiting for reply Cluster request is sent.
Initializing Cluster request is accepted and nodes are initializing.
Running AWS cloud ready to submit jobs.
Waiting for termination Cluster termination request is sent and waiting for termination.
Terminated AWS cloud has terminated.
```
Once the cluster initialization is complete,user@hostnamewill appear next to therunningstatus as shown in
figure below.

You may connect to the master node by using the following SSH command:

ssh user **@hostname**

**1.7.4 Submit a Job**

Similar to regular job submission, follow these steps:

1. Select your pipeline and add into a project
2. Initiate run

On the run page, you should select your active amazon profile as a Run Environment and click “Ready to Run” button.

**1.7.5 Stoping Cluster**

When runs are complete, you can stop cluster by clicking “stop” button on Amazon Management Console. Profile
status will be updated asWaiting for termination, and in few seconds it will be changed toTerminated
as soon as confirmation is received.

### 1.8 Pipeline Examples

There are numerous publicly available pipelines and processes exist in DolphinNext website. In the main page, you
may click onLearn Morebutton and investigate the pipelines you interested. In order to reach process details just
click settings button at the top of the process circles.

**40 Chapter 1. DolphinNext**


**1.8.1 piPipes**

piPipes is a set of pipelines developed in the Zamore Lab and ZLab to analyze piRNA/transposon from different Next
Generation Sequencing libraries such as small RNA-seq, RNA-seq, Genome-seq, ChIP-seq, CAGE/Degradome-Seq.
Please see their Wiki pages (https://github.com/bowhan/piPipes/wiki) for the original content.

These pipelines are loaded to DolphinNext to facilitate their usage. An singularity image is provided
(shub://onuryukselen/piPipes_singularity) where all the neccessary programs are installed to execute the piPipes. Ad-
ditionally, all of the pipelines requires parameterFile in which library specific parameters are defined. You can either
run the default parameters by enteringdefaultas input for the parameterFilePath or you can create a custom parameter
file and enter the full path of it. To download and edit parameter file you can use this link.

Content of the parameterFile.txt is showed at below:

export rRNA_MM=1
export hairpin_MM=1
export genome_MM=1
export transposon_MM=1
export siRNA_bot=20
export siRNA_top=22
export piRNA_bot=23
export piRNA_top=29

Explaination of the variables listed below:

rRNA_MM: Number of mismatches should be allowed **for** rRNA mapping by bowtie
hairpin_MM: Number of mismatches should be allowed **for** microRNA hairping mapping by
˓→bowtie
genome_MM: Number of mismatches should be allowed **for** genome mapping by bowtie
transposon_MM: Number of mismatches should be allowed **for** transposons/piRNAcluster
˓→mapping by bowtie
siRNA_bot: shortest length **for** siRNA
(continues on next page)

**1.8. Pipeline Examples 41**


```
(continued from previous page)
```
siRNA_top: longest length **for** siRNA
piRNA_bot: shortest length **for** piRNA
piRNA_top: longest length **for** piRNA

**42 Chapter 1. DolphinNext**


# CHAPTER 2

## Indices and tables

- genindex
- modindex
- search

#### 43



