params.outdir = 'results'  

params.genome_build = "" //* @dropdown @options:"human_hg19, mouse_mm10, zebrafish_danRer10, zebrafish_GRCz11plus_ensembl, zebrafish_GRCz11ensembl95ucsc, zebrafish_GRCz11refSeqUcsc, rat_rn6, rat_rn6ens, c_elegans_ce11_ws245, mousetest_mm10, custom"
params.run_Tophat = "no" //* @dropdown @options:"yes","no" @show_settings:"Map_Tophat2"
params.run_RSEM = "yes" //* @dropdown @options:"yes","no" @show_settings:"RSEM","CountData_DE"
params.run_HISAT2 = "no" //* @dropdown @options:"yes","no" @show_settings:"Map_HISAT2"
params.run_STAR = "yes" //* @dropdown @options:"yes","no" @show_settings:"Map_STAR"
params.run_IGV_TDF_Conversion = "no" //* @dropdown @options:"yes","no" @show_settings:"IGV_BAM2TDF_converter"
params.run_RSeQC = "no" //* @dropdown @options:"yes","no"
params.run_Picard_CollectMultipleMetrics = "no" //* @dropdown @options:"yes","no"
params.run_BigWig_Conversion = "no" //* @dropdown @options:"yes","no"

_species = ""
_build = ""
_share = ""
//* autofill
if (params.genome_build == "mousetest_mm10"){
    _species = "mousetest"
    _build = "mm10"
} else if (params.genome_build == "human_hg19"){
    _species = "human"
    _build = "hg19"
} else if (params.genome_build == "mouse_mm10"){
    _species = "mouse"
    _build = "mm10"
} else if (params.genome_build == "rat_rn6"){
    _species = "rat"
    _build = "rn6"
} else if (params.genome_build == "rat_rn6ens"){
    _species = "rat"
    _build = "rn6ens"
} else if (params.genome_build == "zebrafish_danRer10"){
    _species = "zebrafish"
    _build = "danRer10"
} else if (params.genome_build == "zebrafish_GRCz11plus_ensembl"){
    _species = "zebrafish"
    _build = "GRCz11plus_ensembl"
} else if (params.genome_build == "zebrafish_GRCz11ensembl95ucsc"){
    _species = "zebrafish"
    _build = "GRCz11ensembl95ucsc"
} else if (params.genome_build == "zebrafish_GRCz11refSeqUcsc"){
    _species = "zebrafish"
    _build = "GRCz11refSeqUcsc"
} else if (params.genome_build == "c_elegans_ce11_ws245"){
    _species = "c_elegans"
    _build = "ce11_ws245"
}

if ($HOSTNAME == "default"){
    _share = "/mnt/efs/share/genome_data"
    $SINGULARITY_IMAGE = "shub://UMMS-Biocore/singularitysc"
    $SINGULARITY_OPTIONS = "--bind /mnt"
}
//* platform
if ($HOSTNAME == "garberwiki.umassmed.edu"){
    _share = "/share/dolphin/genome_data"
    $SINGULARITY_IMAGE = "shub://UMMS-Biocore/singularitysc"
    $SINGULARITY_OPTIONS = "--bind /project --bind /share --bind /nl"
} else if ($HOSTNAME == "ghpcc06.umassrc.org"){
    _share = "/share/data/umw_biocore/genome_data"
    $SINGULARITY_IMAGE = "/project/umw_biocore/singularity/UMMS-Biocore-singularitysc-master-latest.simg"
	$SINGULARITY_OPTIONS = "--bind /project --bind /share --bind /nl"
    $TIME = 700
    $CPU  = 1
    $MEMORY = 32 
    $QUEUE = "long"
} else if ($HOSTNAME == "galaxy.umassmed.edu"){
    _share = "/share/data/umw_biocore/genome_data"
    $SINGULARITY_IMAGE = "/project/umw_biocore/singularity/UMMS-Biocore-singularitysc-master-latest.simg"
	$SINGULARITY_OPTIONS = "--bind /project --bind /share --bind /nl"
    $CPU  = 1
    $MEMORY = 32 
} else if ($HOSTNAME == "biocore.umassmed.edu"){
    _share = "/share/data/umw_biocore/genome_data"
    $SINGULARITY_IMAGE = "/project/umw_biocore/singularity/UMMS-Biocore-singularitysc-master-latest.simg"
	$SINGULARITY_OPTIONS = "--bind /project --bind /share --bind /nl"
    $CPU  = 1
    $MEMORY = 32 
} else if ($HOSTNAME == "fs-e8be58a0"){
    _share = "/mnt/efs/share/genome_data"
    $SINGULARITY_IMAGE = "shub://UMMS-Biocore/singularitysc"
    $SINGULARITY_OPTIONS = "--bind /mnt"
} else if ($HOSTNAME == "fs-bb7510f0"){
    _share = "/mnt/efs/share/genome_data"
    $SINGULARITY_IMAGE = "shub://UMMS-Biocore/singularitysc"
    $SINGULARITY_OPTIONS = "--bind /mnt"
}
//* platform
if (params.genome_build && $HOSTNAME){
    params.genomeDir ="${_share}/${_species}/${_build}/"
    params.genome ="${_share}/${_species}/${_build}/${_build}.fa"
    params.bed_file_genome ="${_share}/${_species}/${_build}/${_build}.bed"
    params.ref_flat ="${_share}/${_species}/${_build}/ref_flat"
    params.genomeSizePath ="${_share}/${_species}/${_build}/${_build}.chrom.sizes"
    params.RSEM_reference_path = "${_share}/${_species}/${_build}/rsem_ref"
    params.RSEM_reference_using_star_path = "${_share}/${_species}/${_build}/rsem_ref_star/rsem_ref_star"
    params.genomeIndexPath ="${_share}/${_species}/${_build}/${_build}"
    params.gtfFilePath ="${_share}/${_species}/${_build}/ucsc.gtf"
    params.bowtieInd_rRNA = "${_share}/${_species}/${_build}/commondb/rRNA/rRNA"
    params.bowtieInd_ercc = "${_share}/${_species}/${_build}/commondb/ercc/ercc"
    params.bowtieInd_miRNA ="${_share}/${_species}/${_build}/commondb/miRNA/miRNA"
    params.bowtieInd_tRNA = "${_share}/${_species}/${_build}/commondb/tRNA/tRNA"
    params.bowtieInd_piRNA = "${_share}/${_species}/${_build}/commondb/piRNA/piRNA"
    params.bowtieInd_snRNA = "${_share}/${_species}/${_build}/commondb/snRNA/snRNA"
    params.bowtieInd_rmsk = "${_share}/${_species}/${_build}/commondb/rmsk/rmsk"
}
if ($HOSTNAME){
	params.trimmomatic_path = "/usr/local/bin/dolphin-bin/trimmomatic-0.32.jar"
    params.fastx_trimmer_path = "/usr/local/bin/dolphin-bin/fastx_trimmer"
    params.igvtools_path = "/usr/local/bin/dolphin-bin/IGVTools/igvtools.jar"
    params.picard_path = "/usr/local/bin/dolphin-bin/picard-tools-1.131/picard.jar"
    params.pdfbox_path = "/usr/local/bin/dolphin-bin/pdfbox-app-2.0.0-RC2.jar"
    params.genomeCoverageBed_path = "/usr/local/bin/dolphin-bin/genomeCoverageBed"
    params.wigToBigWig_path = "/usr/local/bin/dolphin-bin/wigToBigWig"
    params.bowtie2_path = "/usr/local/bin/dolphin-bin/bowtie2"
    params.bowtie_dir = "/usr/local/bin/dolphin-bin"
    params.bowtie2_dir = "/usr/local/bin/dolphin-bin"
    params.star_dir = "/usr/local/bin/dolphin-bin"
    params.RSEM_calculate_expression_path = "/usr/local/bin/dolphin-bin/RSEM-1.2.29/rsem-calculate-expression"
    params.star_path = "/usr/local/bin/dolphin-bin/STAR"
    params.tophat2_path = "/usr/local/bin/dolphin-bin/tophat2_2.0.12/tophat2"
    params.hisat2_path = "/usr/local/bin/dolphin-bin/hisat2/hisat2"
    params.samtools_path = "/usr/local/bin/dolphin-bin/samtools-1.2/samtools"
    params.featureCounts_path = "/usr/bin/subread-1.6.4-Linux-x86_64/bin/featureCounts" 
    params.bowtie_path = "/usr/local/bin/dolphin-bin/bowtie"
    params.umitools_path = "/usr/local/bin/umi_tools"
    params.umi_mark_duplicates_path = "/project/umw_garberlab/yukseleo/piPipes/umi_mark_duplicates.py"
    params.fastx_clipper_path = "/usr/local/bin/dolphin-bin/fastx_toolkit_0.0.13/fastx_clipper"
    params.fastq_quality_filter  = "/usr/local/bin/dolphin-bin/fastx_toolkit_0.0.13/fastq_quality_filter"
}
//*

if (!params.run_FeatureCounts_after_STAR){params.run_FeatureCounts_after_STAR = ""} 
if (!params.run_FeatureCounts_after_Hisat2){params.run_FeatureCounts_after_Hisat2 = ""} 
if (!params.run_FeatureCounts_after_Tophat2){params.run_FeatureCounts_after_Tophat2 = ""} 
if (!params.run_FeatureCounts_after_RSEM){params.run_FeatureCounts_after_RSEM = ""} 
if (!params.mate){params.mate = ""} 
if (!params.reads){params.reads = ""} 

Channel.value(params.run_FeatureCounts_after_STAR).set{g_179_run_featureCounts_g180_113}
Channel.value(params.run_FeatureCounts_after_Hisat2).set{g_188_run_featureCounts_g184_113}
Channel.value(params.run_FeatureCounts_after_Tophat2).set{g_189_run_featureCounts_g190_113}
Channel.value(params.run_FeatureCounts_after_RSEM).set{g_203_run_featureCounts_g178_113}
Channel.value(params.mate).into{g_204_mate_g_127;g_204_mate_g194_3;g_204_mate_g194_1;g_204_mate_g194_10;g_204_mate_g194_11;g_204_mate_g194_14;g_204_mate_g194_16;g_204_mate_g195_25;g_204_mate_g195_26;g_204_mate_g195_30;g_204_mate_g162_13;g_204_mate_g178_82;g_204_mate_g178_95;g_204_mate_g178_111;g_204_mate_g178_120;g_204_mate_g163_3;g_204_mate_g184_82;g_204_mate_g184_95;g_204_mate_g184_111;g_204_mate_g184_120;g_204_mate_g126_5;g_204_mate_g180_82;g_204_mate_g180_95;g_204_mate_g180_111;g_204_mate_g180_120;g_204_mate_g164_0;g_204_mate_g164_3;g_204_mate_g190_82;g_204_mate_g190_95;g_204_mate_g190_111;g_204_mate_g190_120}
Channel
	.fromFilePairs( params.reads , size: (params.mate != "pair") ? 1 : 2 )
	.ifEmpty { error "Cannot find any reads matching: ${params.reads}" }
	.into{g_205_reads_g194_3;g_205_reads_g194_10}


params.gtfFilePath = "" //* @input
params.featureCounts_path = "" //* @input
//* @style @array:{run_name,run_parameters} @multicolumn:{run_name,run_parameters}
process BAM_Analysis_STAR_Prep_featureCounts {

input:
 val run_featureCounts from g_179_run_featureCounts_g180_113

output:
 val run_params  into g180_113_run_parameters_g180_120

when:
run_featureCounts == "yes"

script:
run_name = ["gene_id","transcript_id"] //* @input @title:"Define each of the featureCounts Parameters" @description:"prefix for run output" 
run_parameters = ["-g gene_id -s 0 -Q 20 -T 2 -B -d 50 -D 1000 -C --fracOverlap 0 --minOverlap 1","-g transcript_id -s 0 -Q 20 -T 2 -B -d 50 -D 1000 -C --fracOverlap 0 --minOverlap 1"] //* @input  @description:"-s Indicate strand-specific read counting: 0 (unstranded, default), 1 (stranded) and 2 (reversely stranded) -Q The minimum mapping quality score -T Number of threads -B requireBothEndsMapped -C countChimericFragments −−fracOverlap Minimum fraction of overlapping bases −−minOverlap Minimum number of overlapping bases" 

//define run_name and run_parameters in map item and push into run_params array
run_params = []
for (i = 0; i < run_parameters.size(); i++) {
   map = [:]
   map["run_name"] = run_name[i].replaceAll(" ","_").replaceAll(",","_").replaceAll(";","_").replaceAll("'","_").replaceAll('"',"_")
   map["run_parameters"] = run_parameters[i]
   run_params[i] = map
}
"""
"""
}

params.gtfFilePath = "" //* @input
params.featureCounts_path = "" //* @input
//* @style @array:{run_name,run_parameters} @multicolumn:{run_name,run_parameters}
process BAM_Analysis_Hisat2_Prep_featureCounts {

input:
 val run_featureCounts from g_188_run_featureCounts_g184_113

output:
 val run_params  into g184_113_run_parameters_g184_120

when:
run_featureCounts == "yes"

script:
run_name = ["gene_id","transcript_id"] //* @input @title:"Define each of the featureCounts Parameters" @description:"prefix for run output" 
run_parameters = ["-g gene_id -s 0 -Q 20 -T 2 -B -d 50 -D 1000 -C --fracOverlap 0 --minOverlap 1","-g transcript_id -s 0 -Q 20 -T 2 -B -d 50 -D 1000 -C --fracOverlap 0 --minOverlap 1"] //* @input  @description:"-s Indicate strand-specific read counting: 0 (unstranded, default), 1 (stranded) and 2 (reversely stranded) -Q The minimum mapping quality score -T Number of threads -B requireBothEndsMapped -C countChimericFragments −−fracOverlap Minimum fraction of overlapping bases −−minOverlap Minimum number of overlapping bases" 

//define run_name and run_parameters in map item and push into run_params array
run_params = []
for (i = 0; i < run_parameters.size(); i++) {
   map = [:]
   map["run_name"] = run_name[i].replaceAll(" ","_").replaceAll(",","_").replaceAll(";","_").replaceAll("'","_").replaceAll('"',"_")
   map["run_parameters"] = run_parameters[i]
   run_params[i] = map
}
"""
"""
}

params.gtfFilePath = "" //* @input
params.featureCounts_path = "" //* @input
//* @style @array:{run_name,run_parameters} @multicolumn:{run_name,run_parameters}
process BAM_Analysis_Tophat2_Prep_featureCounts {

input:
 val run_featureCounts from g_189_run_featureCounts_g190_113

output:
 val run_params  into g190_113_run_parameters_g190_120

when:
run_featureCounts == "yes"

script:
run_name = ["gene_id","transcript_id"] //* @input @title:"Define each of the featureCounts Parameters" @description:"prefix for run output" 
run_parameters = ["-g gene_id -s 0 -Q 20 -T 2 -B -d 50 -D 1000 -C --fracOverlap 0 --minOverlap 1","-g transcript_id -s 0 -Q 20 -T 2 -B -d 50 -D 1000 -C --fracOverlap 0 --minOverlap 1"] //* @input  @description:"-s Indicate strand-specific read counting: 0 (unstranded, default), 1 (stranded) and 2 (reversely stranded) -Q The minimum mapping quality score -T Number of threads -B requireBothEndsMapped -C countChimericFragments −−fracOverlap Minimum fraction of overlapping bases −−minOverlap Minimum number of overlapping bases" 

//define run_name and run_parameters in map item and push into run_params array
run_params = []
for (i = 0; i < run_parameters.size(); i++) {
   map = [:]
   map["run_name"] = run_name[i].replaceAll(" ","_").replaceAll(",","_").replaceAll(";","_").replaceAll("'","_").replaceAll('"',"_")
   map["run_parameters"] = run_parameters[i]
   run_params[i] = map
}
"""
"""
}

params.run_FastQC = "no" //* @dropdown @options:"yes","no"
if (params.run_FastQC == "no") { println "INFO: FastQC will be skipped"}

process Adapter_Trimmer_Quality_Module_FastQC {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.(html|zip)$/) "fastqc/$filename"
}

input:
 val mate from g_204_mate_g194_3
 set val(name), file(reads) from g_205_reads_g194_3

output:
 file '*.{html,zip}'  into g194_3_FastQCout_g_177

errorStrategy 'retry'
maxRetries 3

when:
(params.run_FastQC && (params.run_FastQC == "yes"))

script:
nameAll = reads.toString()
if (nameAll.contains('.gz')) {
    file =  nameAll - '.gz' - '.gz'
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    file =  nameAll 
    runGzip = ''
}
"""
${runGzip}
fastqc ${file} 
"""
}

params.gtfFilePath = "" //* @input
params.featureCounts_path = "" //* @input
//* @style @array:{run_name,run_parameters} @multicolumn:{run_name,run_parameters}
process BAM_Analysis_RSEM_Prep_featureCounts {

input:
 val run_featureCounts from g_203_run_featureCounts_g178_113

output:
 val run_params  into g178_113_run_parameters_g178_120

when:
run_featureCounts == "yes"

script:
run_name = ["gene_id","transcript_id"] //* @input @title:"Define each of the featureCounts Parameters" @description:"prefix for run output" 
run_parameters = ["-g gene_id -s 0 -Q 20 -T 2 -B -d 50 -D 1000 -C --fracOverlap 0 --minOverlap 1","-g transcript_id -s 0 -Q 20 -T 2 -B -d 50 -D 1000 -C --fracOverlap 0 --minOverlap 1"] //* @input  @description:"-s Indicate strand-specific read counting: 0 (unstranded, default), 1 (stranded) and 2 (reversely stranded) -Q The minimum mapping quality score -T Number of threads -B requireBothEndsMapped -C countChimericFragments −−fracOverlap Minimum fraction of overlapping bases −−minOverlap Minimum number of overlapping bases" 

//define run_name and run_parameters in map item and push into run_params array
run_params = []
for (i = 0; i < run_parameters.size(); i++) {
   map = [:]
   map["run_name"] = run_name[i].replaceAll(" ","_").replaceAll(",","_").replaceAll(";","_").replaceAll("'","_").replaceAll('"',"_")
   map["run_parameters"] = run_parameters[i]
   run_params[i] = map
}
"""
"""
}

params.run_Adapter_Removal = "no" //* @dropdown @options:"yes","no" @show_settings:"Adapter_Removal"
params.trimmomatic_path = "" //* @input
params.fastx_clipper_path = "" //* @input
Tool_for_Adapter_Removal = "trimmomatic" //* @dropdown @options:"trimmomatic","fastx_clipper" @description:"Choose adapter removal tool to be used. Note: fastx_clipper is not suitable for paired reads." 
Adapter_Sequence = "" //* @textbox @description:"Removes 3' Adapter Sequences. You can enter a single sequence or multiple sequences in different lines. Reverse sequences will not be removed." @tooltip:"Trimmomatic is used for adapter removal" 
//trimmomatic_inputs
min_length = 10 //*  @input @description:"Specifies the minimum length of reads to be kept"
seed_mismatches = 1 //* @input @description:"Specifies the maximum mismatch count which will still allow a full match to be performed"
palindrome_clip_threshold = 30  //* @input @description:"Specifies how accurate the match between the two -adapter ligated- reads must be for PE palindrome read alignment."
simple_clip_threshold = 5 //* @input @description:"specifies how accurate the match between any adapter etc. sequence must be against a read"

//fastx_clipper_inputs
discard_non_clipped = "yes" //* @dropdown @options:"yes","no" @description:"-c: discard_non_clipped sequences (keep only sequences which contained the adapter)"

//* @style @multicolumn:{seed_mismatches, palindrome_clip_threshold, simple_clip_threshold} @condition:{Tool_for_Adapter_Removal="trimmomatic", seed_mismatches, palindrome_clip_threshold, simple_clip_threshold}, {Tool_for_Adapter_Removal="fastx_clipper", discard_non_clipped}
if (!((params.run_Adapter_Removal && (params.run_Adapter_Removal == "yes")) || !params.run_Adapter_Removal)){
g_205_reads_g194_10.into{g194_10_reads_g194_1}
g194_10_log_file_g194_11 = Channel.empty()
} else {

process Adapter_Trimmer_Quality_Module_Adapter_Removal {

input:
 set val(name), file(reads) from g_205_reads_g194_10
 val mate from g_204_mate_g194_10

output:
 set val(name), file("reads/*")  into g194_10_reads_g194_1
 file "*.fastx.log" optional true  into g194_10_log_file_g194_11

when:
(params.run_Adapter_Removal && (params.run_Adapter_Removal == "yes")) || !params.run_Adapter_Removal

shell:
discard_non_clipped_text = ""
if (discard_non_clipped == "yes") {discard_non_clipped_text = "-c"}
nameAll = reads.toString()
nameArray = nameAll.split(' ')
file2 = ""
if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file1 =  nameArray[0] - '.gz' 
    if (mate == "pair") {file2 =  nameArray[1] - '.gz'}
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file1 =  nameArray[0]
    if (mate == "pair") {file2 =  nameArray[1]}
    runGzip = ''
}
'''
#!/usr/bin/env perl
 use List::Util qw[min max];
 use strict;
 use File::Basename;
 use Getopt::Long;
 use Pod::Usage; 
 
system("mkdir reads adapter unpaired");

open(OUT, ">adapter/adapter.fa");
my @adaps=split(/\n/,"!{Adapter_Sequence}");
my $i=1;
foreach my $adap (@adaps)
{
 print OUT ">adapter$i\\n$adap\\n";
 $i++;
}
close(OUT);

my $cmd = "";

system("!{runGzip}");
my $quality="33";
my $format="";
my ($format, $len)=getFormat("!{file1}");
print "fastq format: $format\\n";
if ($format eq "solexa"){   
    $quality="64";
}
print "fastq quality: $quality\\n";
print "tool: !{Tool_for_Adapter_Removal}\\n";
$cmd="java -jar !{params.trimmomatic_path}";
if ("!{mate}" eq "pair") {
    if ("!{Tool_for_Adapter_Removal}" eq "trimmomatic") {
        system("$cmd PE -threads 1 -phred64 -trimlog !{name}.log !{file1} !{file2} reads/!{name}.1.fastq unpaired/!{name}.1.fastq.unpaired reads/!{name}.2.fastq unpaired/!{name}.1.fastq.unpaired ILLUMINACLIP:adapter/adapter.fa:!{seed_mismatches}:!{palindrome_clip_threshold}:!{simple_clip_threshold} MINLEN:!{min_length}");
    } elsif ("!{Tool_for_Adapter_Removal}" eq "fastx_clipper") {
        print "Fastx_clipper is not suitable for paired reads.";
    }
} else {
    if ("!{Tool_for_Adapter_Removal}" eq "trimmomatic") {
        print "$cmd SE -threads 1 -phred64 -trimlog !{name}.log !{file1} reads/!{name}.fastq ILLUMINACLIP:adapter/adapter.fa:!{seed_mismatches}:!{palindrome_clip_threshold}:!{simple_clip_threshold} MINLEN:!{min_length}";
        system("$cmd SE -threads 1 -phred64 -trimlog !{name}.log !{file1} reads/!{name}.fastq ILLUMINACLIP:adapter/adapter.fa:!{seed_mismatches}:!{palindrome_clip_threshold}:!{simple_clip_threshold} MINLEN:!{min_length}");
    } elsif ("!{Tool_for_Adapter_Removal}" eq "fastx_clipper") {
        print "!{params.fastx_clipper_path}  -Q $quality -a !{Adapter_Sequence} -l !{min_length} !{discard_non_clipped_text} -v -i !{file1} -o reads/!{name}.fastq > !{name}.fastx.log";
        system("!{params.fastx_clipper_path}  -Q $quality -a !{Adapter_Sequence} -l !{min_length} !{discard_non_clipped_text} -v -i !{file1} -o reads/!{name}.fastq > !{name}.fastx.log");
    }
}


# automatic format detection
sub getFormat
{
   my ($filename)=@_;

   # set function variables
   open (IN, $filename);
   my $j=1;
   my $qualities="";
   my $len=0;
   while(my $line=<IN>)
   {
     chomp($line);
     if ($j >50) { last;}
     if ($j%4==0)
     {
        $qualities.=$line;
        $len=length($line);
     }
     $j++;
   }
   close(IN);
  
   my $format = "";

   # set regular expressions
   my $sanger_regexp = qr/[!"#$%&'()*+,-.\\/0123456789:]/;
   my $solexa_regexp = qr/[\\;<=>\\?]/;
   my $solill_regexp = qr/[JKLMNOPQRSTUVWXYZ\\[\\]\\^\\_\\`abcdefgh]/;
   my $all_regexp = qr/[\\@ABCDEFGHI]/;

   # set counters
   my $sanger_counter = 0;
   my $solexa_counter = 0;
   my $solill_counter = 0;

   # check qualities
   if( $qualities =~ m/$sanger_regexp/ ){
          $sanger_counter = 1;
   }
   if( $qualities =~ m/$solexa_regexp/ ){
          $solexa_counter = 1;
   }
   if( $qualities =~ m/$solill_regexp/ ){
          $solill_counter = 1;
   }

   # determine format
   if( $sanger_counter ){
        $format = "sanger";
    }elsif($solexa_counter ){
        $format = "solexa";
    }elsif($solill_counter ){
        $format = "illumina";
    }

    # return file format
    return( $format,$len );
}
'''
}
}



process Adapter_Trimmer_Quality_Module_Adapter_Removal_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /adapter_removal_summary.tsv$/) "adapter_removal/$filename"
	else if (filename =~ /adapter_removal_detailed_summary.tsv$/) "adapter_removal_detailed_summary/$filename"
}

input:
 file logfile from g194_10_log_file_g194_11.collect()
 val mate from g_204_mate_g194_11

output:
 file "adapter_removal_summary.tsv"  into g194_11_outputFileTSV_g_198
 file "adapter_removal_detailed_summary.tsv"  into g194_11_outputFile

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage;
use Data::Dumper;

my @header;
my %all_files;
my %tsv;
my %tsvDetail;
my %headerHash;
my %headerText;
my %headerTextDetail;

my $i = 0;
chomp( my $contents = `ls *.fastx.log` );
my @files = split( /[\\n]+/, $contents );
foreach my $file (@files) {
    $i++;
    my $mapper   = "fastx";
    my $mapOrder = "1";
    $file =~ /(.*).fastx\\.log/;
    my $name = $1;    ##sample name
    push( @header, "fastx" );

    my $in;
    my $out;
    my $tooshort;
    my $adapteronly;
    my $noncliped;
    my $Nreads;

    chomp( $in =`cat $file | grep 'Input:' | awk '{sum+=\\$2} END {print sum}'` );
    chomp( $out =`cat $file | grep 'Output:' | awk '{sum+=\\$2} END {print sum}'` );
    chomp( $tooshort =`cat $file | grep 'too-short reads' | awk '{sum+=\\$2} END {print sum}'`);
    chomp( $adapteronly =`cat $file | grep 'adapter-only reads' | awk '{sum+=\\$2} END {print sum}'`);
    chomp( $noncliped =`cat $file | grep 'non-clipped reads.' | awk '{sum+=\\$2} END {print sum}'`);
    chomp( $Nreads =`cat $file | grep 'N reads.' | awk '{sum+=\\$2} END {print sum}'` );

    $tsv{$name}{$mapper} = [ $in, $out ];
    $headerHash{$mapOrder} = $mapper;
    $headerText{$mapOrder} = [ "Total Reads", "Reads After Adapter Removal" ];
    $tsvDetail{$name}{$mapper} =
      [ $in, $tooshort, $adapteronly, $noncliped, $Nreads, $out ];
    $headerTextDetail{$mapOrder} = [
        "Total Reads",
        "Too-short reads",
        "Adapter-only reads",
        "Non-clipped reads",
        "N reads",
        "Reads After Adapter Removal"
    ];
}

my @mapOrderArray = ( keys %headerHash );
my @sortedOrderArray = sort { $a <=> $b } @mapOrderArray;

my $summary          = "adapter_removal_summary.tsv";
my $detailed_summary = "adapter_removal_detailed_summary.tsv";
writeFile( $summary,          \\%headerText,       \\%tsv );
writeFile( $detailed_summary, \\%headerTextDetail, \\%tsvDetail );

sub writeFile {
    my $summary    = $_[0];
    my %headerText = %{ $_[1] };
    my %tsv        = %{ $_[2] };
    open( OUT, ">$summary" );
    print OUT "Sample\\t";
    my @headArr = ();
    for my $mapOrder (@sortedOrderArray) {
        push( @headArr, @{ $headerText{$mapOrder} } );
    }
    my $headArrAll = join( "\\t", @headArr );
    print OUT "$headArrAll\\n";

    foreach my $name ( keys %tsv ) {
        my @rowArr = ();
        for my $mapOrder (@sortedOrderArray) {
            push( @rowArr, @{ $tsv{$name}{ $headerHash{$mapOrder} } } );
        }
        my $rowArrAll = join( "\\t", @rowArr );
        print OUT "$name\\t$rowArrAll\\n";
    }
    close(OUT);
}

'''
}

params.run_Trimmer = "no" //* @dropdown @options:"yes","no" @show_settings:"Trimmer"
if (params.run_Trimmer != "yes") {println "INFO: Trimmer will be skipped"}
params.fastx_trimmer_path = "" //* @input 
single_or_paired_end_reads = "" //* @dropdown @options:"single","pair" 
trim_length_5prime = 0 //* @input @description:"Trimming length from 5' end" @tooltip:"Fastx trimmer (Version: 0.0.13) is used for trimming" 
trim_length_3prime = 0 //* @input @description:"Trimming length from 3' end" @tooltip:"Fastx trimmer (Version: 0.0.13) is used for trimming" 
trim_length_5prime_R1 = 0 //* @input @description:"Trimming length from 5' end of R1 reads" @tooltip:"Fastx trimmer (Version: 0.0.13) is used for trimming" 
trim_length_3prime_R1 = 0 //* @input @description:"Trimming length from 3' end of R1 reads" @tooltip:"Fastx trimmer (Version: 0.0.13) is used for trimming" 
trim_length_5prime_R2 = 0 //* @input @description:"Trimming length from 5' end of R2 reads" @tooltip:"Fastx trimmer (Version: 0.0.13) is used for trimming" 
trim_length_3prime_R2 = 0 //* @input @description:"Trimming length from 3' end of R2 reads" @tooltip:"Fastx trimmer (Version: 0.0.13) is used for trimming" 

//* @style @multicolumn:{trim_length_5prime,trim_length_3prime}, {trim_length_5prime_R1,trim_length_3prime_R1}, {trim_length_5prime_R2,trim_length_3prime_R2} @condition:{single_or_paired_end_reads="single", trim_length_5prime,trim_length_3prime}, {single_or_paired_end_reads="pair", trim_length_5prime_R1,trim_length_3prime_R1,trim_length_5prime_R2,trim_length_3prime_R2}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
if (!((params.run_Trimmer && (params.run_Trimmer == "yes")) || !params.run_Trimmer)){
g194_10_reads_g194_1.into{g194_1_reads_g194_14}
} else {

process Adapter_Trimmer_Quality_Module_Trimmer {

input:
 set val(name), file(reads) from g194_10_reads_g194_1
 val mate from g_204_mate_g194_1

output:
 set val(name), file("reads/*")  into g194_1_reads_g194_14

when:
(params.run_Trimmer && (params.run_Trimmer == "yes")) || !params.run_Trimmer

shell:
nameAll = reads.toString()
nameArray = nameAll.split(' ')
file2 = ""
if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file1 =  nameArray[0] - '.gz' 
    if (mate == "pair") {file2 =  nameArray[1] - '.gz'}
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file1 =  nameArray[0]
    if (mate == "pair") {file2 =  nameArray[1]}
    runGzip = ''
}
'''
#!/usr/bin/env perl
 use List::Util qw[min max];
 use strict;
 use File::Basename;
 use Getopt::Long;
 use Pod::Usage; 
 
system("mkdir reads");

system("!{runGzip}");
if ("!{mate}" eq "pair") {
    my $file1 = "!{file1}";
    my $file2 = "!{file2}";
    my $trim1 = "!{trim_length_5prime_R1}:!{trim_length_3prime_R1}";
    my $trim2 = "!{trim_length_5prime_R2}:!{trim_length_3prime_R2}";
    my ($format, $len)=getFormat($file1);
    print "length of $file1: $len\\n";
    trimFiles($file1, $trim1, $format, $len);
    my ($format, $len)=getFormat($file2);
    print "length of $file2: $len\\n";
    trimFiles($file2, $trim2, $format, $len);
} else {
    my $file1 = "!{file1}";
    my $trim1 = "!{trim_length_5prime}:!{trim_length_3prime}";
    my ($format, $len)=getFormat($file1);
    print "length of file1 $len\\n";
    trimFiles($file1, $trim1, $format, $len);
}



sub trimFiles
{
  my ($file, $trim, $format, $len)=@_;
  my $cmd="!{params.fastx_trimmer_path}";
    my @nts=split(/[,:\\s\\t]+/,$trim);
    my $inpfile="";
    my $com="";
    my $i=1;
    my $outfile="";
    my $param="";
    my $quality="";
    #if ($format eq "sanger")
    #{   
      $quality="-Q33";
    #}
    if (scalar(@nts)==2)
    {
      $param = "-f ".($nts[0]+1) if (exists($nts[0]) && $nts[0] >= 0 );
      $param .= " -l ".($len-$nts[1]) if (exists($nts[0]) && $nts[1] > 0 );
      print "parameters for $file: $param \\n ";
      $outfile="reads/$file";  
      $com="$cmd $quality -v $param -o $outfile -i $file " if ((exists($nts[0]) && $nts[0] > 0) || (exists($nts[0]) && $nts[1] > 0 ));
      print "$com\\n";
      if ($com eq ""){
          print "trimmer skipped for $file \\n";
          system("mv $file reads/.");
      } else {
          system("$com");
          print "trimmer executed for $file \\n";
      }
    }

    
}

# automatic format detection
sub getFormat
{
   my ($filename)=@_;

   # set function variables
   open (IN, $filename);
   my $j=1;
   my $qualities="";
   my $len=0;
   while(my $line=<IN>)
   {
     chomp($line);
     if ($j >50) { last;}
     if ($j%4==0)
     {
        $qualities.=$line;
        $len=length($line);
     }
     $j++;
   }
   close(IN);
  
   my $format = "";

   # set regular expressions
   my $sanger_regexp = qr/[!"#$%&'()*+,-.\\/0123456789:]/;
   my $solexa_regexp = qr/[\\;<=>\\?]/;
   my $solill_regexp = qr/[JKLMNOPQRSTUVWXYZ\\[\\]\\^\\_\\`abcdefgh]/;
   my $all_regexp = qr/[\\@ABCDEFGHI]/;

   # set counters
   my $sanger_counter = 0;
   my $solexa_counter = 0;
   my $solill_counter = 0;

   # check qualities
   if( $qualities =~ m/$sanger_regexp/ ){
          $sanger_counter = 1;
   }
   if( $qualities =~ m/$solexa_regexp/ ){
          $solexa_counter = 1;
   }
   if( $qualities =~ m/$solill_regexp/ ){
          $solill_counter = 1;
   }

   # determine format
   if( $sanger_counter ){
        $format = "sanger";
    }elsif($solexa_counter ){
        $format = "solexa";
    }elsif($solill_counter ){
        $format = "illumina";
    }

    # return file format
    return( $format,$len );
}

'''
}
}


params.run_Quality_Filtering = "no" //* @dropdown @options:"yes","no" @show_settings:"Quality_Filtering"
if (params.run_Quality_Filtering != "yes") {println "INFO: Quality_Filtering will be skipped"}
params.trimmomatic_path = "" //* @input
params.fastq_quality_filter = "" //* @input
tool = "trimmomatic" //* @dropdown @options:"trimmomatic","fastx" @description:"Choose quality removal tool to be used. Note:fastx option (fastx_toolkit_0.0.13 fastq_quality_filter) is not suitable for paired reads." 
window_size = 10 //* @input @description:"Performs a sliding window trimming approach. It starts scanning at the 5' end and clips the read once the average quality within the window falls below a threshold (=required_quality). " @tooltip:"Trimmomatic 0.32 is used for quality filtering" 
required_quality_for_window_trimming = 15 //* @input @description:"specifies the average quality required for window trimming approach" @tooltip:"Trimmomatic 0.32 is used for quality filtering" 
leading = 5 //* @input @description:"Cut bases off the start of a read, if below a threshold quality" @tooltip:"Trimmomatic 0.32 is used for quality filtering" 
trailing = 5 //* @input @description:"Cut bases off the end of a read, if below a threshold quality" @tooltip:"Trimmomatic 0.32 is used for quality filtering" 
minlen = 36 //* @input @description:"Specifies the minimum length of reads to be kept" @tooltip:"Trimmomatic 0.32 is used for quality filtering" 

// fastx parameters
minQuality = 20 //* @input @description:"Minimum quality score to keep reads"
minPercent = 100 //* @input @description:"Minimum percent of bases that must have entered minQuality"
//* @style @multicolumn:{window_size,required_quality}, {leading,trailing,minlen}, {minQuality,minPercent} @condition:{tool="trimmomatic", minlen, trailing, leading, required_quality_for_window_trimming, window_size}, {tool="fastx", minQuality, minPercent}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
if (!((params.run_Quality_Filtering && (params.run_Quality_Filtering == "yes")) || !params.run_Quality_Filtering)){
g194_1_reads_g194_14.into{g194_14_reads_g195_25}
g194_14_log_file_g194_16 = Channel.empty()
} else {

process Adapter_Trimmer_Quality_Module_Quality_Filtering {

input:
 set val(name), file(reads) from g194_1_reads_g194_14
 val mate from g_204_mate_g194_14

output:
 set val(name), file("reads/*")  into g194_14_reads_g195_25
 file "*.fastx_quality.log" optional true  into g194_14_log_file_g194_16

when:
(params.run_Quality_Filtering && (params.run_Quality_Filtering == "yes")) || !params.run_Quality_Filtering    

shell:
nameAll = reads.toString()
nameArray = nameAll.split(' ')
file2 ="";
if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file1 =  nameArray[0] - '.gz' 
    if (mate == "pair") {file2 =  nameArray[1] - '.gz'}
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file1 =  nameArray[0]
    if (mate == "pair") {file2 =  nameArray[1]}
    runGzip = ''
}
'''
#!/usr/bin/env perl
 use List::Util qw[min max];
 use strict;
 use File::Basename;
 use Getopt::Long;
 use Pod::Usage; 
 
system("mkdir reads unpaired");


system("!{runGzip}");
my $param = "SLIDINGWINDOW:"."!{window_size}".":"."!{required_quality_for_window_trimming}";
$param.=" LEADING:"."!{leading}";
$param.=" TRAILING:"."!{trailing}";
$param.=" MINLEN:"."!{minlen}";
my $format=getFormat("!{file1}");
print "fastq format: $format\\n";
my $quality="33";
if ($format eq "sanger"){   
    $quality="33";
} elsif ($format eq "illumina" || $format eq "solexa"){
    $quality="64";
} 
print "fastq quality: $quality\\n";
     
my $cmd="java -jar !{params.trimmomatic_path}";
if ("!{tool}" eq "trimmomatic") {
    if ("!{mate}" eq "pair") {
        system("$cmd PE -threads 1 -phred${quality} -trimlog !{name}.log !{file1} !{file2} reads/!{name}.1.fastq unpaired/!{name}.1.fastq.unpaired reads/!{name}.2.fastq unpaired/!{name}.1.fastq.unpaired $param");
    } else {
        system("$cmd SE -threads 1 -phred${quality} -trimlog !{name}.log !{file1} reads/!{name}.fastq $param");
    }
} elsif ("!{tool}" eq "fastx") {
    if ("!{mate}" eq "pair") {
        print("WARNING: Fastx option is not suitable for paired reads. This step will be skipped.");
        system("mv !{file1} !{file2} reads/.");
    } else {
        system("!{params.fastq_quality_filter}  -Q $quality -q !{minQuality} -p !{minPercent} -v -i !{file1} -o reads/!{name}.fastq > !{name}.fastx_quality.log");
    }
    
}


# automatic format detection
sub getFormat
{
   my ($filename)=@_;

   # set function variables
   open (IN, $filename);
   my $j=1;
   my $qualities="";
   while(my $line=<IN>)
   {
     if ($j >50) { last;}
     if ($j%4==0)
     {
        $qualities.=$line;
     }
     $j++;
   }
   close(IN);
  
   my $format = "";

   # set regular expressions
   my $sanger_regexp = qr/[!"#$%&'()*+,-.\\/0123456789:]/;
   my $solexa_regexp = qr/[\\;<=>\\?]/;
   my $solill_regexp = qr/[JKLMNOPQRSTUVWXYZ\\[\\]\\^\\_\\`abcdefgh]/;
   my $all_regexp = qr/[\\@ABCDEFGHI]/;

   # set counters
   my $sanger_counter = 0;
   my $solexa_counter = 0;
   my $solill_counter = 0;

   # check qualities
   if( $qualities =~ m/$sanger_regexp/ ){
          $sanger_counter = 1;
   }
   if( $qualities =~ m/$solexa_regexp/ ){
          $solexa_counter = 1;
   }
   if( $qualities =~ m/$solill_regexp/ ){
          $solill_counter = 1;
   }

   # determine format
   if( $sanger_counter ){
        $format = "sanger";
    }elsif($solexa_counter ){
        $format = "solexa";
    }elsif($solill_counter ){
        $format = "illumina";
    }

    # return file format
    return( $format );
}

'''
}
}


params.umi_mark_duplicates_path = "" //* @input
params.picard_path = "" //* @input
params.bowtie_path = "" //* @input
params.bowtie2_path = "" //* @input
params.star_path = "" //* @input
params.samtools_path = "" //* @input
params.bowtieInd_rRNA = "" //* @input
params.bowtieInd_ercc = "" //* @input
params.bowtieInd_miRNA = "" //* @input
params.bowtieInd_tRNA = "" //* @input
params.bowtieInd_piRNA = "" //* @input
params.bowtieInd_snRNA = "" //* @input
params.bowtieInd_rmsk = "" //* @input
params.genomeIndexPath = //* @input
params.run_Sequential_Mapping = "no" //* @dropdown @options:"yes","no" @show_settings:"Sequential_Mapping"

bowtieIndexes = [rRNA: params.bowtieInd_rRNA, 
                 ercc: params.bowtieInd_ercc,
                 miRNA: params.bowtieInd_miRNA,
                 tRNA: params.bowtieInd_tRNA,
                 piRNA: params.bowtieInd_piRNA,
                 snRNA: params.bowtieInd_snRNA,
                 rmsk: params.bowtieInd_rmsk,
                 genome: params.genomeIndexPath]


//_nucleicAcidType="dna" should be defined in the autofill section of pipeline header in case dna is used.
remove_duplicates = "no" //* @dropdown @description:"Duplicates (both PCR and optical) will be removed from alignment file (bam) and separate count table will be created for comparison" @title:"General Mapping Options" @options:{"yes","no"}
remove_duplicates_based_on_UMI_after_mapping = "no" //* @dropdown @description:"UMI extract process should have executed before this step. Read headers should have UMI tags which are separated with underscore.(eg. NS5HGY:2:11_GTATAACCTT)" @options:{"yes","no"}


_select_sequence = "" //* @dropdown @description:"Select sequence for mapping" @title:"Sequence Set for Mapping" @options:{"rRNA","ercc","miRNA","tRNA","piRNA","snRNA","rmsk","genome",custom"},{_nucleicAcidType="dna","ercc","rmsk","genome","custom"}
index_directory  = "" //* @input  @description:"index directory of sequence(full path)" @tooltip:"The index directory must include the full path and the name of the index file must only be the prefix of the fasta or index file. Index files and Fasta files also need to have the same prefix.For STAR alignment, gtf file which has the same prefix, must be found in same directory" 
name_of_the_index_file = "" //* @input  @autofill:{_select_sequence=("rRNA","ercc","miRNA","tRNA","piRNA","snRNA","rmsk","genome"), _select_sequence},{_select_sequence="custom", " "} @description:"Name of the index or fasta file (prefix)" @tooltip:"The index directory must include the full path and the name of the index file must only be the prefix of the fasta or index file. Index files and Fasta files also need to have the same prefix.For STAR alignment, gtf file which has the same prefix, must be found in same directory" 
_aligner = "bowtie2" //* @dropdown @description:"Select aligner tool"  @options:{_select_sequence=("rRNA","ercc","miRNA","tRNA","piRNA","snRNA","rmsk"),"bowtie","bowtie2"},{_select_sequence=("genome","custom"),"bowtie","bowtie2","STAR"}
aligner_Parameters = "" //* @input @description:"Aligner parameters." @autofill:{_aligner="bowtie", "--threads 1"},{_aligner="bowtie2", "-N 1"},{_aligner="STAR", "--runThreadN 1"} 
description = "" //* @input @autofill:{_select_sequence=("rRNA","ercc","miRNA","tRNA","piRNA","snRNA","rmsk","genome"), _select_sequence},{_select_sequence="custom", " "} @description:"Description of index file (please don't use comma or quotes in this field" 
filter_Out = "Yes" //* @dropdown @dropdown @options:"Yes","No" @description:"Select whether or not you want the reads mapped to this index filtered out of your total reads." 

desc_all=[]
description.eachWithIndex() {param,i -> 
    if (param.isEmpty()){
        desc_all[i] = name_of_the_index_file[i]
    }  else {
        desc_all[i] = param.replaceAll("[ |.|;]", "_")
    }
}
custom_index=[]
index_directory.eachWithIndex() {param,i -> 
    if (_select_sequence[i] != "custom"){
        custom_index[i] = bowtieIndexes[_select_sequence[i]]
    }  else {
        custom_index[i] = param+"/"+name_of_the_index_file[i] 
    }
}

mapList = []
paramList = []
alignerList = []
filterList = []
indexList = []

//concat default mapping and custom mapping
mapList = (desc_all) 
paramList = (aligner_Parameters)
alignerList = (_aligner)
filterList = (filter_Out)
indexList = (custom_index)

mappingList = mapList.join(" ") // convert into space separated format in order to use in bash for loop
paramsList = paramList.join(",") // convert into comma separated format in order to use in as array in bash
alignersList = alignerList.join(",") 
filtersList = filterList.join(",") 
indexesList = indexList.join(",") 
//* @style @condition:{remove_duplicates="yes",remove_duplicates_based_on_UMI_after_mapping},{remove_duplicates="no"},{_select_sequence="custom", index_directory,name_of_the_index_file,description,_aligner,aligner_Parameters,filter_Out},{_select_sequence=("rRNA","ercc","miRNA","tRNA","piRNA","snRNA","rmsk","genome"),_aligner,aligner_Parameters,filter_Out}  @array:{_select_sequence,_select_sequence, index_directory,name_of_the_index_file,_aligner,aligner_Parameters,filter_Out,description} @multicolumn:{_select_sequence,_select_sequence,index_directory,name_of_the_index_file,_aligner,aligner_Parameters,filter_Out, description},{remove_duplicates,remove_duplicates_based_on_UMI_after_mapping}


//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 32
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 2500
    $CPU  = 1
    $MEMORY = 32
    $QUEUE = "long"
}
//* platform
//* autofill
if (!(params.run_Sequential_Mapping == "yes")){
g194_14_reads_g195_25.into{g195_25_reads_g_127; g195_25_reads_g162_13}
g195_25_bowfiles_g195_26 = Channel.empty()
g195_25_bowfiles_g_177 = Channel.empty()
g195_25_bam_file_g195_23 = Channel.empty()
g195_25_bam_file_g195_27 = Channel.empty()
g195_25_bam_index_g195_23 = Channel.empty()
g195_25_bam_index_g195_27 = Channel.empty()
g195_25_filter_g195_26 = Channel.empty()
g195_25_log_file_g195_30 = Channel.empty()
} else {

process Sequential_Mapping_Module_Sequential_Mapping {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*\/.*_sorted.bam$/) "sequential_mapping/$filename"
	else if (filename =~ /.*\/.*_sorted.bam.bai$/) "sequential_mapping/$filename"
	else if (filename =~ /.*\/.*_duplicates_stats.log$/) "sequential_mapping/$filename"
}

input:
 set val(name), file(reads) from g194_14_reads_g195_25
 val mate from g_204_mate_g195_25

output:
 set val(name), file("final_reads/*")  into g195_25_reads_g_127, g195_25_reads_g162_13
 set val(name), file("bowfiles/*") optional true  into g195_25_bowfiles_g195_26, g195_25_bowfiles_g_177
 file "*/*_sorted.bam"  into g195_25_bam_file_g195_23
 file "*/*_sorted.bam.bai"  into g195_25_bam_index_g195_23
 val filtersList  into g195_25_filter_g195_26
 file "*/*_sorted.dedup.bam" optional true  into g195_25_bam_file_g195_27
 file "*/*_sorted.dedup.bam.bai" optional true  into g195_25_bam_index_g195_27
 file "*/*_duplicates_stats.log" optional true  into g195_25_log_file_g195_30

when:
params.run_Sequential_Mapping == "yes"

script:
nameAll = reads.toString()
nameArray = nameAll.split(' ')
def file2;

if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file1 =  nameArray[0] - '.gz' 
    if (mate == "pair") {file2 =  nameArray[1] - '.gz'}
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file1 =  nameArray[0]
    if (mate == "pair") {file2 =  nameArray[1]}
    runGzip = ''
}

"""
#!/bin/bash
mkdir reads final_reads bowfiles 
if [ -n "${mappingList}" ]; then
    $runGzip
    #rename files to standart format
    if [ "${mate}" == "pair" ]; then
        mv $file1 ${name}.1.fastq 2>/dev/null
        mv $file2 ${name}.2.fastq 2>/dev/null
        mv ${name}.1.fastq ${name}.2.fastq reads/.
    else
        mv $file1 ${name}.fastq 2>/dev/null
        mv ${name}.fastq reads/.
    fi
    #sequential mapping
    k=0
    prev="reads"
    IFS=',' read -r -a paramsListAr <<< "${paramsList}" #create comma separated array 
    IFS=',' read -r -a filtersListAr <<< "${filtersList}"
    IFS=',' read -r -a indexesListAr <<< "${indexesList}"
    IFS=',' read -r -a alignersListAr <<< "${alignersList}"
    wrkDir=\$(pwd)
    for rna_set in ${mappingList}
    do
        ((k++))
        printf -v k2 "%02d" "\$k" #turn into two digit format
        mkdir -p \${rna_set}/unmapped
        cd \$rna_set
        if [ "\${filtersListAr[\$k-1]}" == "Yes" ]; then
            ln -s \${wrkDir}/\${prev}/* .
            prev=\${rna_set}/unmapped
        else
            ln -s \${wrkDir}/\${prev}/* .
        fi
        genomeDir=`dirname "\${indexesListAr[\$k-1]}"`
        echo "INFO: genomeDir: \$genomeDir"
        if [ -e "\${indexesListAr[\$k-1]}.1.bt2" -o  -e "\${indexesListAr[\$k-1]}.fa"  -o  -e "\${indexesListAr[\$k-1]}.fasta"  -o  -e "\$genomeDir/SAindex" ]; then
            if [ -e "\${indexesListAr[\$k-1]}.fa" ] ; then
                fasta=\${indexesListAr[\$k-1]}.fa
            elif [ -e "\${indexesListAr[\$k-1]}.fasta" ] ; then
                fasta=\${indexesListAr[\$k-1]}.fasta
            fi
            if [ -e "\${indexesListAr[\$k-1]}.1.bt2" -a "\${alignersListAr[\$k-1]}" == "bowtie2" ] ; then
                echo "INFO: \${indexesListAr[\$k-1]}.1.bt2 Bowtie2 index found."
            elif [ -e "\${indexesListAr[\$k-1]}.1.ebwt" -a "\${alignersListAr[\$k-1]}" == "bowtie" ] ; then
                echo "INFO: \${indexesListAr[\$k-1]}.1.ebwt Bowtie index found."
            elif [ -e "\$genomeDir/SAindex" -a "\${alignersListAr[\$k-1]}" == "STAR" ] ; then
                echo "INFO: \$genomeDir/SAindex STAR index found."
            elif [ -e "\${indexesListAr[\$k-1]}.fa" -o  -e "\${indexesListAr[\$k-1]}.fasta" ] ; then
                if [ "\${alignersListAr[\$k-1]}" == "bowtie2" ]; then
                    ${params.bowtie2_path}-build \$fasta \${indexesListAr[\$k-1]}
                elif [ "\${alignersListAr[\$k-1]}" == "STAR" ]; then
                    if [ -e "\${indexesListAr[\$k-1]}.gtf" ]; then
                        ${params.star_path} --runMode genomeGenerate --genomeDir \$genomeDir --genomeFastaFiles \$fasta --sjdbGTFfile \${indexesListAr[\$k-1]}.gtf --genomeSAindexNbases 5
                    else
                        echo "WARNING: \${indexesListAr[\$k-1]}.gtf not found. STAR index is not generated."
                    fi
                elif [ "\${alignersListAr[\$k-1]}" == "bowtie" ]; then
                    ${params.bowtie_path}-build \$fasta \${indexesListAr[\$k-1]}
                fi
            fi
                
            if [ "${mate}" == "pair" ]; then
                if [ "\${alignersListAr[\$k-1]}" == "bowtie2" ]; then
                    ${params.bowtie2_path} \${paramsListAr[\$k-1]} -x \${indexesListAr[\$k-1]} --no-unal --un-conc unmapped/${name}.unmapped.fastq -1 ${name}.1.fastq -2 ${name}.2.fastq --al-conc ${name}.fq.mapped -S \${rna_set}_${name}_alignment.sam > \${k2}_${name}.bow_\${rna_set}  2>&1
                elif [ "\${alignersListAr[\$k-1]}" == "STAR" ]; then
                    ${params.star_path} \${paramsListAr[\$k-1]}  --genomeDir \$genomeDir --readFilesIn ${name}.1.fastq ${name}.2.fastq --outSAMtype SAM  --outFileNamePrefix ${name}.star --outReadsUnmapped Fastx
                    mv ${name}.starAligned.out.sam \${rna_set}_${name}_alignment.sam
                    mv ${name}.starUnmapped.out.mate1 unmapped/${name}.unmapped.1.fastq
                    mv ${name}.starUnmapped.out.mate2 unmapped/${name}.unmapped.2.fastq
                    mv ${name}.starLog.final.out \${k2}_${name}.star_\${rna_set}
                elif [ "\${alignersListAr[\$k-1]}" == "bowtie" ]; then
                    ${params.bowtie_path} \${paramsListAr[\$k-1]}  --fr \${indexesListAr[\$k-1]}  --un  unmapped/${name}.unmapped.fastq -1 ${name}.1.fastq -2 ${name}.2.fastq -S  \${rna_set}_${name}_alignment.sam > \${k2}_${name}.bow1_\${rna_set}  2>&1
                    mv unmapped/${name}.unmapped_1.fastq unmapped/${name}.unmapped.1.fastq
                    mv unmapped/${name}.unmapped_2.fastq unmapped/${name}.unmapped.2.fastq
                fi
            else
                if [ "\${alignersListAr[\$k-1]}" == "bowtie2" ]; then
                    ${params.bowtie2_path} \${paramsListAr[\$k-1]} -x \${indexesListAr[\$k-1]} --no-unal --un  unmapped/${name}.unmapped.fastq -U ${name}.fastq --al ${name}.fq.mapped -S \${rna_set}_${name}_alignment.sam > \${k2}_${name}.bow_\${rna_set}  2>&1
                elif [ "\${alignersListAr[\$k-1]}" == "STAR" ]; then
                    ${params.star_path} \${paramsListAr[\$k-1]}  --genomeDir \$genomeDir --readFilesIn ${name}.fastq --outSAMtype SAM  --outFileNamePrefix ${name}.star --outReadsUnmapped Fastx
                    mv ${name}.starAligned.out.sam \${rna_set}_${name}_alignment.sam
                    mv ${name}.starUnmapped.out.mate1 unmapped/${name}.unmapped.fastq
                    mv ${name}.starLog.final.out \${k2}_${name}.star_\${rna_set}
                elif [ "\${alignersListAr[\$k-1]}" == "bowtie" ]; then
                    ${params.bowtie_path} \${paramsListAr[\$k-1]}  \${indexesListAr[\$k-1]}  --un  unmapped/${name}.unmapped.fastq  ${name}.fastq  -S \${rna_set}_${name}_alignment.sam > \${k2}_${name}.bow1_\${rna_set}  2>&1
                    
                fi
            fi
            ${params.samtools_path} view -bT \$fasta \${rna_set}_${name}_alignment.sam > \${rna_set}_${name}_alignment.bam
            if [ "\${alignersListAr[\$k-1]}" == "bowtie" ]; then
                mv \${rna_set}_${name}_alignment.bam \${rna_set}_${name}_tmp0.bam
                ${params.samtools_path} view -F 0x04 -b \${rna_set}_${name}_tmp0.bam > \${rna_set}_${name}_alignment.bam  # Remove unmapped reads
                if [ "${mate}" == "pair" ]; then
                    echo "# unique mapped reads: \$(${params.samtools_path} view -f 0x40 -F 0x4 -q 255 \${rna_set}_${name}_alignment.bam | cut -f 1 | sort | uniq | wc -l)" >> \${k2}_${name}.bow1_\${rna_set}
                else
                    echo "# unique mapped reads: \$(${params.samtools_path} view -F 0x40 -q 255 \${rna_set}_${name}_alignment.bam | cut -f 1 | sort | uniq | wc -l)" >> \${k2}_${name}.bow1_\${rna_set}
                fi
            fi
            if [ "${mate}" == "pair" ]; then
                mv \${rna_set}_${name}_alignment.bam \${rna_set}_${name}_alignment.tmp1.bam
                ${params.samtools_path} sort -n \${rna_set}_${name}_alignment.tmp1.bam \${rna_set}_${name}_alignment.tmp2
                ${params.samtools_path} view -bf 0x02 \${rna_set}_${name}_alignment.tmp2.bam >\${rna_set}_${name}_alignment.bam
                rm \${rna_set}_${name}_alignment.tmp1.bam \${rna_set}_${name}_alignment.tmp2.bam
            fi
            ${params.samtools_path} sort \${rna_set}_${name}_alignment.bam \${rna_set}@${name}_sorted
            ${params.samtools_path} index \${rna_set}@${name}_sorted.bam
            
            if [ "${remove_duplicates}" == "yes" ]; then
                ## check read header whether they have UMI tags which are separated with underscore.(eg. NS5HGY:2:11_GTATAACCTT)
                umiCheck=\$(${params.samtools_path} view \${rna_set}@${name}_sorted.bam |head -n 1 | awk 'BEGIN {FS="\\t"}; {print \$1}' | awk 'BEGIN {FS=":"}; \$NF ~ /_/ {print \$NF}')
                
                # based on remove_duplicates_based_on_UMI_after_mapping
                if [ "${remove_duplicates_based_on_UMI_after_mapping}" == "yes" -a ! -z "\$umiCheck" ]; then
                    echo "INFO: ${params.umi_mark_duplicates_path} will be executed for removing duplicates from bam file"
                    python ${params.umi_mark_duplicates_path} -f \${rna_set}@${name}_sorted.bam -p 4
                else
                    echo "INFO: Picard MarkDuplicates will be executed for removing duplicates from bam file"
                    if [ "${remove_duplicates_based_on_UMI_after_mapping}" == "yes"  ]; then
                        echo "WARNING: Read header have no UMI tags which are separated with underscore. Picard MarkDuplicates will be executed to remove duplicates from alignment file (bam) instead of remove_duplicates_based_on_UMI_after_mapping."
                    fi
                    java -jar ${params.picard_path} MarkDuplicates OUTPUT=\${rna_set}@${name}_sorted.deumi.sorted.bam METRICS_FILE=${name}_picard_PCR_duplicates.log  VALIDATION_STRINGENCY=LENIENT REMOVE_DUPLICATES=false INPUT=\${rna_set}@${name}_sorted.bam 
                fi
                #get duplicates stats (read the sam flags)
                ${params.samtools_path} flagstat \${rna_set}@${name}_sorted.deumi.sorted.bam > \${k2}@\${rna_set}@${name}_duplicates_stats.log
                #remove alignments marked as duplicates
                ${params.samtools_path} view -b -F 0x400 \${rna_set}@${name}_sorted.deumi.sorted.bam > \${rna_set}@${name}_sorted.deumi.sorted.bam.x_dup
                #sort deduplicated files by chrom pos
                ${params.samtools_path} sort \${rna_set}@${name}_sorted.deumi.sorted.bam.x_dup \${rna_set}@${name}_sorted.dedup
                ${params.samtools_path} index \${rna_set}@${name}_sorted.dedup.bam
            fi
            
        
            for file in unmapped/*; do mv \$file \${file/.unmapped/}; done ##remove .unmapped from filename
            if [ "\${alignersListAr[\$k-1]}" == "bowtie2" ]; then
                grep -v Warning \${k2}_${name}.bow_\${rna_set} > ${name}.tmp
                mv ${name}.tmp \${k2}_${name}.bow_\${rna_set}
                cp \${k2}_${name}.bow_\${rna_set} ./../bowfiles/.
            elif [ "\${alignersListAr[\$k-1]}" == "bowtie" ]; then
                cp \${k2}_${name}.bow1_\${rna_set} ./../bowfiles/.
            elif [ "\${alignersListAr[\$k-1]}" == "STAR" ]; then
                cp \${k2}_${name}.star_\${rna_set} ./../bowfiles/.
            fi
            cd ..
        else
            echo "WARNING: \${indexesListAr[\$k-1]} Mapping skipped. File not found."
            cd unmapped 
            ln -s \${wrkDir}/\${rna_set}/*fastq .
            cd ..
            cd ..
        fi
    done
    cd final_reads && ln -s \${wrkDir}/\${prev}/* .
else 
    mv ${reads} final_reads/.
fi
"""
}
}


params.RSEM_reference_path = "" //* @input
params.RSEM_reference_using_star_path = "" //* @input
params.RSEM_calculate_expression_path = "" //* @input
params.bowtie_dir = "" //* @input
params.bowtie2_dir = "" //* @input
params.star_dir = "" //* @input
RSEM_reference_type = "star" //* @dropdown @options:"bowtie","bowtie2","star"
RSEM_parameters = "-p 4" //* @input @description:"RSEM parameters" @tooltip:"RSEM v1.2.28 is used" 
no_bam_output = "true" //* @dropdown @options:"true","false" @description:"If true is selected, RSEM will not output any BAM file (default=false)"
output_genome_bam = "true" //* @dropdown @options:"true","false" @description:"If true is selected, RSEM will generate a BAM file, with alignments mapped to genomic coordinates and annotated with their posterior probabilities. (default=true)"
//* @style @condition:{no_bam_output="false", output_genome_bam}, {no_bam_output="true"} @multicolumn:{no_bam_output, output_genome_bam}

//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 3
    $MEMORY = 32
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 3000
    $CPU  = 4
    $MEMORY = 32
    $QUEUE = "long"
}
//* platform
//* autofill

process RSEM_module_RSEM {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /pipe.rsem..*$/) "rsem/$filename"
}

input:
 val mate from g_204_mate_g162_13
 set val(name), file(reads) from g195_25_reads_g162_13

output:
 file "pipe.rsem.*"  into g162_13_rsemOut_g162_15, g162_13_rsemOut_g162_17, g162_13_rsemOut_g_177
 set val(name), file("pipe.rsem.*/*.genome.bam") optional true  into g162_13_bam_file_g178_109, g162_13_bam_file_g178_110, g162_13_bam_file_g178_111, g162_13_bam_file_g178_112, g162_13_bam_file_g178_120
 set val(name), file("pipe.rsem.*/*.bam") optional true  into g162_13_mapped_reads

when:
(params.run_RSEM && (params.run_RSEM == "yes")) || !params.run_RSEM

script:
nameAll = reads.toString()
nameArray = nameAll.split(' ')
def file2;

if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file1 =  nameArray[0] - '.gz' 
    if (mate == "pair") {file2 =  nameArray[1] - '.gz'}
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file1 =  nameArray[0]
    if (mate == "pair") {file2 =  nameArray[1]}
    runGzip = ''
}
genome_BamText = (output_genome_bam.toString() != "false") ? "--output-genome-bam" : ""
if (no_bam_output.toString() != "false"){
    noBamText = "--no-bam-output"
    genome_BamText = ""
} else {
    noBamText = ""
}

rsemRefText = ""
rsemRef = ""
if (RSEM_reference_type == "star"){
    rsemRefText = "--star --star-path "+ params.star_dir
    rsemRef = params.RSEM_reference_using_star_path
} else if (RSEM_reference_type == "bowtie2"){
    rsemRefText = "--bowtie2 --bowtie2-path "+ params.bowtie2_dir
    rsemRef = params.RSEM_reference_path
} else if (RSEM_reference_type == "bowtie"){
    rsemRefText = "--bowtie-path "+ params.bowtie_dir
    rsemRef = params.RSEM_reference_path
}
"""
$runGzip
mkdir -p pipe.rsem.${name}

if [ "${mate}" == "pair" ]; then
    echo "${params.RSEM_calculate_expression_path} ${rsemRefText} ${RSEM_parameters} ${genome_BamText} ${noBamText} --paired-end ${file1} ${file2} ${rsemRef} pipe.rsem.${name}/rsem.out.${name}"
    perl ${params.RSEM_calculate_expression_path} ${rsemRefText} ${RSEM_parameters} ${genome_BamText} ${noBamText} --paired-end ${file1} ${file2} ${rsemRef} pipe.rsem.${name}/rsem.out.${name}
else
    echo "${params.RSEM_calculate_expression_path} ${rsemRefText} ${RSEM_parameters} ${genome_BamText} ${noBamText} --calc-ci ${file1} ${rsemRef} pipe.rsem.${name}/rsem.out.${name}"
    perl ${params.RSEM_calculate_expression_path} ${rsemRefText} ${RSEM_parameters} ${genome_BamText} ${noBamText} --calc-ci ${file1} ${rsemRef} pipe.rsem.${name}/rsem.out.${name}
fi
"""
}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 30
    $CPU  = 1
    $MEMORY = 10
    $QUEUE = "short"
}
//* platform
//* autofill
process RSEM_module_RSEM_Alignment_Summary {

input:
 file rsemDir from g162_13_rsemOut_g162_17.collect()

output:
 file "rsem_alignment_sum.tsv"  into g162_17_outputFileTSV_g_198

shell:
'''

#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage;
use Data::Dumper;
my $indir = $ENV{'PWD'};

opendir D, $indir or die "Could not open $indir";
my @alndirs = sort { $a cmp $b } grep /^pipe/, readdir(D);
closedir D;

my @a=();
my %b=();
my %c=();
my $i=0;
my @headers = ();
my %tsv;
foreach my $d (@alndirs){
    my $dir = "${indir}/$d";
    my $libname=$d;
    $libname=~s/pipe\\.rsem\\.//;
    my $multimapped;
    my $aligned;
    my $total;
    
    chomp($total = `awk 'NR == 1 {print \\$4}' ${dir}/rsem.out.$libname.stat/rsem.out.$libname.cnt`);
    chomp($aligned = `awk 'NR == 1 {print \\$2}' ${dir}/rsem.out.$libname.stat/rsem.out.$libname.cnt`);
    chomp($multimapped = `awk 'NR == 2 {print \\$2}' ${dir}/rsem.out.$libname.stat/rsem.out.$libname.cnt`);
    $tsv{$libname}=[$libname, $total];
    push(@{$tsv{$libname}}, $multimapped);
    push(@{$tsv{$libname}}, (int($aligned) - int($multimapped))."");
}


push(@headers, "Sample");
push(@headers, "Total Reads");
push(@headers, "Multimapped Reads Aligned (RSEM)");
push(@headers, "Unique Aligned Reads (RSEM)");


my @keys = keys %tsv;
my $summary = "rsem_alignment_sum.tsv";
my $header_string = join("\\t", @headers);
`echo "$header_string" > $summary`;
foreach my $key (@keys){
    my $values = join("\\t", @{ $tsv{$key} });
        `echo "$values" >> $summary`;
}
'''
}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 30
    $CPU  = 1
    $MEMORY = 10
    $QUEUE = "short"
}
//* platform
//* autofill
process RSEM_module_RSEM_Count {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tsv$/) "rsem_summary/$filename"
}

input:
 file rsemOut from g162_13_rsemOut_g162_15.collect()

output:
 file "*.tsv"  into g162_15_outputFile
 val wdir  into g162_15_wdir_g162_14

shell:
wdir="rsem_summary/genes_expression_expected_count.tsv"
'''
#!/usr/bin/env perl

my %tf = (
        expected_count => 4,
        tpm => 5,
        fpkm => 6,
    );

my $indir = $ENV{'PWD'};
$outdir = $ENV{'PWD'};


my @gene_iso_ar = ("genes", "isoforms");
my @tpm_fpkm_expectedCount_ar = ("expected_count", "tpm");
for($l = 0; $l <= $#gene_iso_ar; $l++) {
    my $gene_iso = $gene_iso_ar[$l];
    for($ll = 0; $ll <= $#tpm_fpkm_expectedCount_ar; $ll++) {
        my $tpm_fpkm_expectedCount = $tpm_fpkm_expectedCount_ar[$ll];

        opendir D, $indir or die "Could not open $indir\n";
        my @alndirs = sort { $a cmp $b } grep /^pipe/, readdir(D);
        closedir D;
    
        my @a=();
        my %b=();
        my %c=();
        my $i=0;
        foreach my $d (@alndirs){ 
            my $dir = "${indir}/$d";
            print $d."\n";
            my $libname=$d;
            $libname=~s/pipe\\.rsem\\.//;
    
            $i++;
            $a[$i]=$libname;
            open IN,"${dir}/rsem.out.$libname.$gene_iso.results";
            $_=<IN>;
            while(<IN>)
            {
                my @v=split; 
                $b{$v[0]}{$i}=$v[$tf{$tpm_fpkm_expectedCount}];
                $c{$v[0]}=$v[1];
            }
            close IN;
        }
        my $outfile="${indir}/"."$gene_iso"."_expression_"."$tpm_fpkm_expectedCount".".tsv";
        open OUT, ">$outfile";
        if ($gene_iso ne "isoforms") {
            print OUT "gene\ttranscript";
        } else {
            print OUT "transcript\tgene";
        }
    
        for(my $j=1;$j<=$i;$j++) {
            print OUT "\t$a[$j]";
        }
        print OUT "\n";
    
        foreach my $key (keys %b) {
            print OUT "$key\t$c{$key}";
            for(my $j=1;$j<=$i;$j++){
                print OUT "\t$b{$key}{$j}";
            }
            print OUT "\n";
        }
        close OUT;
    }
}
'''
}

cols = "" //* @input @description:"Comma separated columns that are going to be used in DE"
conds = "" //* @input @description:"Comma separated conditions that each condition correspond to the columns"
cols = convertCommaSepString(cols)
conds = convertCommaSepString(conds)

// convert comma separated string into comma quote and comma separated string
//eg. "a, b, c" -> "a","b","c"
def convertCommaSepString( t ) {
    commaSepList=t.split(",").collect{ '"' + it.trim() + '"'}
    c=commaSepList.toString()
    //remove first and last brackets
    return c.substring(1, c.length()-1)
}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 10
    $CPU  = 1
    $MEMORY = 10
    $QUEUE = "short"
}
//* platform
//* autofill
process RSEM_module_CountData_DE {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.rmd$/) "rsem_rmarkdown/$filename"
}

input:
 val wdir from g162_15_wdir_g162_14

output:
 file "*.rmd"  into g162_14_rMarkdown

shell:
'''
#!/usr/bin/env perl

my $script = <<'EOF';
## Count data DE analysis

[1]. Reading the data.

The merged the count dara table will be read from a web URL to be able 
to run this rmarkdown anywhere. 
In Eukaryotes only a subset of all genes are expressed in 
a given cell. Expression is therefore a bimodal distribution, 
with non-expressed genes having counts that result from experimental 
and biological noise. It is important to filter out the genes 
that are not expressed before doing differential gene expression. 
You can decide which cutoff separates expressed vs non-expressed 
genes by looking your histogram we created.


```{r, echo=FALSE, message=FALSE}
library(debrowser)
library(plotly)
source("https://dolphinnext.umassmed.edu/dist/scripts/funcs.R")
library(RCurl)
url<-{{webpath:"!{wdir}"}}
file <- textConnection(getURL(url)) 
rsem <- read.table(file,sep="\\t", header=TRUE, row.names=1) 
data <- data.frame(rsem[,sapply(rsem, is.numeric)]) 

cols<- c(!{cols})

data <- data[, cols]

h <- hist(log10(rowSums(data)), breaks = as.numeric(100), plot = FALSE) 

plot_ly(x = h$mids, y = h$counts, width = 500, height=300) %>% 
layout( title = "Histogram") %>%
add_bars()
``` 

[2]. All2all scatter plots

To check the reproducibility of biological replicates, we use all2all plots.

```{r, echo=FALSE, message=FALSE}
all2all(data)
``` 

[3]. DESeq ANALYSIS

The goal of Differential gene expression analysis is to find 
genes or transcripts whose difference in expression, when accounting 
for the variance within condition, is higher than expected by chance. 

The first step is to indicate the condition that each column (experiment) 
in the table represent. 
Here we define the correspondence between columns and conditions. 
Make sure the order of the columns matches to your table.

In this case a total sum of 10 counts separates well expressed 
from non-expressed genes. You can change this value and padj value and 
log2FoldChange cutoffs according to your data

```{r, echo=FALSE, message=FALSE}
conds <- factor( c(!{conds}) )
avgall<-cbind(rowSums(data[cols[conds == levels(conds)[1]]])/3, 
              rowSums(data[cols[conds == levels(conds)[2]]])/3)
colnames(avgall)<-c(levels(conds)[1], levels(conds)[2])

gdat<-data.frame(avgall)
de_res <- runDESeq(data, cols, conds,  padj=0.01, log2FoldChange=1, non_expressed_cutoff=10)
overlaid_data <- overlaySig(gdat, de_res$res_selected)
ggplot() +
  geom_point(data=overlaid_data, aes_string(x=levels(conds)[1], y=levels(conds)[2],
                                            colour="Legend"), alpha=6/10, size=3) +
  scale_colour_manual(values=c("All"="darkgrey","Significant"="red"))+
  scale_x_log10() +scale_y_log10()
```

[4]. MA Plot

The Second way to visualize it, we use MA plots.
For MA Plot there is another builtin function that you can use

```{r, echo=FALSE, message=FALSE}
plotMA(de_res$res_detected,ylim=c(-2,2),main="DESeq2");
```

[5]. Volcano Plot

The third way of visualizing the data is making a Volcano Plot.
Here on the x axis you have log2foldChange values and y axis you 
have your -log10 padj values. To see how significant genes are 
distributed. Highlight genes that have an absolute fold change > 2 
and a padj < 0.01

```{r, echo=FALSE, message=FALSE}
volcanoPlot(de_res,  padj=0.01, log2FoldChange=1)
```

[6] Heatmap

The forth way of visualizing the data that is widely used in this 
type of analysis is clustering and Heatmaps.

```{r, echo=FALSE, message=FALSE}
sel_data<-data[rownames(de_res$res_selected),]
norm_data<-getNormalizedMatrix(sel_data, method="TMM")
ld <- log2(norm_data+0.1)
cldt <- scale(t(ld), center=TRUE, scale=TRUE);
cld <- t(cldt)
dissimilarity <- 1 - cor(cld)
distance <- as.dist(dissimilarity)
heatmap.2(cld, Rowv=TRUE,dendrogram="column",
          Colv=TRUE, col=redblue(256),labRow=NA,
          density.info="none",trace="none", cexCol=0.8,
          hclust=function(x) hclust(x,method="complete"),
          distfun=function(x) as.dist((1-cor(t(x)))/2))

```
EOF

open OUT, ">rmark.rmd";
print OUT $script;
close OUT;
'''


}

params.gtfFilePath = "" //* @input
params.featureCounts_path = "" //* @input
process BAM_Analysis_RSEM_featureCounts {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*$/) "featureCounts_after_rsem/$filename"
}

input:
 set val(name), file(bam) from g162_13_bam_file_g178_120
 val paired from g_204_mate_g178_120
 each run_params from g178_113_run_parameters_g178_120

output:
 file "*"  into g178_120_outputFileTSV_g178_117

script:
pairText = ""
if (paired == "pair"){
    pairText = "-p"
}

run_name = run_params["run_name"] 
run_parameters = run_params["run_parameters"] 

"""
${params.featureCounts_path} ${pairText} ${run_parameters} -a ${params.gtfFilePath} -o ${name}@${run_name}@fCounts.txt ${bam}
## remove first line
sed -i '1d' ${name}@${run_name}@fCounts.txt

"""
}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 30
    $CPU  = 1
    $MEMORY = 10
    $QUEUE = "short"
}
//* platform
//* autofill
process BAM_Analysis_RSEM_summary_featureCounts {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_featureCounts.tsv$/) "featureCounts_after_rsem_summary/$filename"
	else if (filename =~ /.*_featureCounts.sum.tsv$/) "featureCounts_after_rsem_details/$filename"
}

input:
 file featureCountsOut from g178_120_outputFileTSV_g178_117.collect()

output:
 file "*_featureCounts.tsv"  into g178_117_outputFile
 file "*_featureCounts.sum.tsv"  into g178_117_outFileTSV

shell:
'''
#!/usr/bin/env perl

# Step 1: Merge count files
my %tf = ( expected_count => 6 );
my @run_name=();
chomp(my $contents = `ls *@fCounts.txt`);
my @files = split(/[\\n]+/, $contents);
foreach my $file (@files){
    $file=~/(.*)\\@(.*)\\@fCounts\\.txt/;
    my $runname = $2;
    push(@run_name, $runname) unless grep{$_ eq $runname} @run_name;
}


my @expectedCount_ar = ("expected_count");
for($l = 0; $l <= $#run_name; $l++) {
    my $runName = $run_name[$l];
    for($ll = 0; $ll <= $#expectedCount_ar; $ll++) {
        my $expectedCount = $expectedCount_ar[$ll];
    
        my @a=();
        my %b=();
        my %c=();
        my $i=0;
        chomp(my $contents = `ls *\\@${runName}\\@fCounts.txt`);
        my @files = split(/[\\n]+/, $contents);
        foreach my $file (@files){
        $i++;
        $file=~/(.*)\\@${runName}\\@fCounts\\.txt/;
        my $libname = $1; 
        $a[$i]=$libname;
        open IN, $file;
            $_=<IN>;
            while(<IN>){
                my @v=split; 
                $b{$v[0]}{$i}=$v[$tf{$expectedCount}];
                $c{$v[0]}=$v[5]; #length column
            }
            close IN;
        }
        my $outfile="$runName"."_featureCounts.tsv";
        open OUT, ">$outfile";
        if ($runName eq "transcript_id") {
            print OUT "transcript\tlength";
        } else {
            print OUT "gene\tlength";
        }
    
        for(my $j=1;$j<=$i;$j++) {
            print OUT "\t$a[$j]";
        }
        print OUT "\n";
    
        foreach my $key (keys %b) {
            print OUT "$key\t$c{$key}";
            for(my $j=1;$j<=$i;$j++){
                print OUT "\t$b{$key}{$j}";
            }
            print OUT "\n";
        }
        close OUT;
    }
}

# Step 2: Merge summary files
for($l = 0; $l <= $#run_name; $l++) {
    my $runName = $run_name[$l];
    my @a=();
    my %b=();
    my $i=0;
    chomp(my $contents = `ls *\\@${runName}\\@fCounts.txt.summary`);
    my @files = split(/[\\n]+/, $contents);
    foreach my $file (@files){
        $i++;
        $file=~/(.*)\\@${runName}\\@fCounts\\.txt\\.summary/;
        my $libname = $1; 
        $a[$i]=$libname;
        open IN, $file;
        $_=<IN>;
        while(<IN>){
            my @v=split; 
            $b{$v[0]}{$i}=$v[1];
        }
        close IN;
    }
    my $outfile="$runName"."_featureCounts.sum.tsv";
    open OUT, ">$outfile";
    print OUT "criteria";
    for(my $j=1;$j<=$i;$j++) {
        print OUT "\t$a[$j]";
    }
    print OUT "\n";
    
    foreach my $key (keys %b) {
        print OUT "$key";
        for(my $j=1;$j<=$i;$j++){
            print OUT "\t$b{$key}{$j}";
        }
        print OUT "\n";
    }
    close OUT;
}

'''
}

params.bed_file_genome = "" //* @input
process BAM_Analysis_RSEM_RSeQC {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /result\/.*.out$/) "rseqc_rsem/$filename"
}

input:
 set val(name), file(bam) from g162_13_bam_file_g178_110

output:
 file "result/*.out"  into g178_110_outputFileOut_g178_95, g178_110_outputFileOut_g_177

when:
(params.run_RSeQC && (params.run_RSeQC == "yes")) || !params.run_RSeQC

script:
"""
mkdir result
read_distribution.py  -i ${bam} -r ${params.bed_file_genome}> result/RSeQC.${name}.out
"""
}


process BAM_Analysis_RSEM_RSeQC_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tsv$/) "rseqc_summary_rsem/$filename"
}

input:
 file rseqcOut from g178_110_outputFileOut_g178_95.collect()
 val mate from g_204_mate_g178_95

output:
 file "*.tsv"  into g178_95_outputFileTSV

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

my $indir = $ENV{'PWD'};
my $outd = $ENV{'PWD'};
my @files = ();
my @outtypes = ("RSeQC");
my @order=( "Total Reads", "Total Tags" , "Total Assigned Tags", "CDS_Exons", "5'UTR_Exons", "3'UTR_Exons", "Introns", "TSS_up_1kb", "TSS_up_5kb", "TSS_up_10kb", "TES_down_1kb", "TES_down_5kb", "TES_down_10kb");
my %lines=(
  "Total Reads" => 1,
  "Total Tags" => 1,
  "Total Assigned Tags" => 1,
  "CDS_Exons" => 2,
  "5'UTR_Exons" => 2,
  "3'UTR_Exons" => 2,
  "Introns" => 2,
  "TSS_up_1kb" => 2,
  "TSS_up_5kb" => 2,
  "TSS_up_10kb" => 2,
  "TES_down_1kb" => 2,
  "TES_down_5kb" => 2,
  "TES_down_10kb" => 2
);


foreach my $outtype (@outtypes)
{

my $ext=".out";
@files = <$indir/$outtype*$ext>;

my @rowheaders=();
my @libs=();
my %vals=();
my %normvals=();
my $type = "rsem";

foreach my $d (@files){
  my $libname=basename($d, $ext);
  $libname=~s/RSeQC.//g;
  $libname=~s/rsem.out.//g;
  $libname=~s/.genome//g;
  print $libname."\\n";
  push(@libs, $libname); 
  getVals($d, $libname, \\%vals, \\%normvals, \\%lines);
}
#print Dumper(%vals);
#print Dumper(%normvals);

my $sizemetrics = keys %vals;
write_results("$outd/$outtype.$type.counts.tsv", \\@libs,\\%vals, \\@order, "region") if ($sizemetrics>0);
write_results("$outd/$outtype.$type.tagskb.tsv", \\@libs,\\%normvals, \\@order, "region") if ($sizemetrics>0);

}

sub write_results
{
  my ($outfile, $libs, $vals, $order, $name )=@_;
  open(OUT, ">$outfile");
  print OUT "$name\\t".join("\\t", @{$libs})."\\n";

  my $lib=${$libs}[0];
  foreach my $key ( @order )
  {
    if (exists ${$vals}{$lib}{$key}) {
    print OUT $key;
    foreach my $lib (@{$libs})
    {
      print OUT "\\t".${$vals}{$lib}{$key};
    } 
    print OUT "\\n";
    }
  }
  close(OUT);
}

sub getVals{
  my ($filename, $libname, $vals, $normvals, $lines)=@_;
  if (-e $filename){
     open(IN, $filename);
     while(my $line=<IN>)
     {
       chomp($line);
       my @vals_arr=split(/\\s{2,}/,$line);
       if (exists ${$lines}{$vals_arr[0]}) {
         my $idx=${$lines}{$vals_arr[0]};
         ${$vals}{$libname}{$vals_arr[0]}=$vals_arr[$idx] if (exists $vals_arr[$idx]);
         if ($idx==2) {
             ${$normvals}{$libname}{$vals_arr[0]}=$vals_arr[3] if (exists $vals_arr[3]);
         }
       }
     } 
  }
  
}
'''

}

params.pdfbox_path = "" //* @input
params.picard_path = "" //* @input
//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 32
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 32
    $QUEUE = "short"
}
//* platform
//* autofill
process BAM_Analysis_RSEM_Picard {

input:
 set val(name), file(bam) from g162_13_bam_file_g178_109

output:
 file "*_metrics"  into g178_109_outputFileOut_g178_82
 file "results/*.pdf"  into g178_109_outputFilePdf_g178_82

when:
(params.run_Picard_CollectMultipleMetrics && (params.run_Picard_CollectMultipleMetrics == "yes")) || !params.run_Picard_CollectMultipleMetrics

script:
"""
java -jar ${params.picard_path} CollectMultipleMetrics OUTPUT=${name}_multiple.out VALIDATION_STRINGENCY=LENIENT INPUT=${bam}
mkdir results && java -jar ${params.pdfbox_path} PDFMerger *.pdf results/${name}_multi_metrics.pdf
"""
}


process BAM_Analysis_RSEM_Picard_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tsv$/) "picard_summary_rsem/$filename"
	else if (filename =~ /results\/.*.pdf$/) "picard_summary_pdf_rsem/$filename"
}

input:
 file picardOut from g178_109_outputFileOut_g178_82.collect()
 val mate from g_204_mate_g178_82
 file picardPdf from g178_109_outputFilePdf_g178_82.collect()

output:
 file "*.tsv"  into g178_82_outputFileTSV
 file "results/*.pdf"  into g178_82_outputFilePdf

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

system("mkdir results && mv *.pdf results/. ");

my $indir = $ENV{'PWD'};
my $outd = $ENV{'PWD'};
my @files = ();
my @outtypes = ("CollectRnaSeqMetrics", "alignment_summary_metrics", "base_distribution_by_cycle_metrics", "insert_size_metrics", "quality_by_cycle_metrics", "quality_distribution_metrics" );

foreach my $outtype (@outtypes)
{
my $ext="_multiple.out";
$ext.=".$outtype" if ($outtype ne "CollectRnaSeqMetrics");
@files = <$indir/*$ext>;

my @rowheaders=();
my @libs=();
my %metricvals=();
my %histvals=();

my $pdffile="";
my $libname="";
foreach my $d (@files){
  my $libname=basename($d, $ext);
  print $libname."\\n";
  push(@libs, $libname); 
  getMetricVals($d, $libname, \\%metricvals, \\%histvals, \\@rowheaders);
}

my $sizemetrics = keys %metricvals;
write_results("$outd/$outtype.stats.tsv", \\@libs,\\%metricvals, \\@rowheaders, "metric") if ($sizemetrics>0);
my $sizehist = keys %histvals;
write_results("$outd/$outtype.hist.tsv", \\@libs,\\%histvals, "none", "nt") if ($sizehist>0);

}

sub write_results
{
  my ($outfile, $libs, $vals, $rowheaders, $name )=@_;
  open(OUT, ">$outfile");
  print OUT "$name\\t".join("\\t", @{$libs})."\\n";
  my $size=0;
  $size=scalar(@{${$vals}{${$libs}[0]}}) if(exists ${$libs}[0] and exists ${$vals}{${$libs}[0]} );
  
  for (my $i=0; $i<$size;$i++)
  { 
    my $rowname=$i;
    $rowname = ${$rowheaders}[$i] if ($name=~/metric/);
    print OUT $rowname;
    foreach my $lib (@{$libs})
    {
      print OUT "\\t".${${$vals}{$lib}}[$i];
    } 
    print OUT "\\n";
  }
  close(OUT);
}

sub getMetricVals{
  my ($filename, $libname, $metricvals, $histvals,$rowheaders)=@_;
  if (-e $filename){
     my $nextisheader=0;
     my $nextisvals=0;
     my $nexthist=0;
     open(IN, $filename);
     while(my $line=<IN>)
     {
       chomp($line);
       @{$rowheaders}=split(/\\t/, $line) if ($nextisheader && !scalar(@{$rowheaders})); 
       if ($nextisvals) {
         @{${$metricvals}{$libname}}=split(/\\t/, $line);
         $nextisvals=0;
       }
       if($nexthist){
          my @vals=split(/[\\s\\t]+/,$line); 
          push(@{${$histvals}{$libname}}, $vals[1]) if (exists $vals[1]);
       }
       $nextisvals=1 if ($nextisheader); $nextisheader=0;
       $nextisheader=1 if ($line=~/METRICS CLASS/);
       $nexthist=1 if ($line=~/normalized_position/);
     } 
  }
  
}
'''

}

params.genomeCoverageBed_path = "" //* @input
params.wigToBigWig_path = "" //* @input
params.genomeSizePath = "" //* @input
process BAM_Analysis_RSEM_UCSC_BAM2BigWig_converter {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.bw$/) "bigwig_rsem/$filename"
}

input:
 set val(name), file(bam) from g162_13_bam_file_g178_112

output:
 file "*.bw"  into g178_112_outputFileBw

when:
(params.run_BigWig_Conversion && (params.run_BigWig_Conversion == "yes")) || !params.run_BigWig_Conversion

script:
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = ''
} else {
    runSamtools = "samtools sort " +bam+ " "+ name +"_sorted && samtools index "+ name+"_sorted.bam "
}
"""
$runSamtools
${params.genomeCoverageBed_path} -split -bg -ibam ${name}_sorted.bam -g ${params.genomeSizePath} > ${name}.bg 
${params.wigToBigWig_path} -clip -itemsPerSlot=1 ${name}.bg ${params.genomeSizePath} ${name}.bw 
"""
}

igv_extention_factor = "0" //* @input @description:"The read or feature is extended by the specified distance in bp prior to counting. This option is useful for chip-seq and rna-seq applications. The value is generally set to the average fragment length of the library minus the average read length." @tooltip:"igvtools is used"
igv_window_size = "5" //* @input  @description:"The window size over which coverage is averaged." @tooltip:"igvtools is used"

params.genome = "" //* @input
params.igvtools_path = "" //* @input
process BAM_Analysis_RSEM_IGV_BAM2TDF_converter {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tdf$/) "igvtools_rsem/$filename"
}

input:
 val mate from g_204_mate_g178_111
 set val(name), file(bam) from g162_13_bam_file_g178_111

output:
 file "*.tdf"  into g178_111_outputFileOut

when:
(params.run_IGV_TDF_Conversion && (params.run_IGV_TDF_Conversion == "yes")) || !params.run_IGV_TDF_Conversion

script:
pairedText = (params.nucleicAcidType == "dna" && mate == "pair") ? " --pairs " : ""
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = ''
} else {
    runSamtools = "samtools sort " +bam+ " "+ name +"_sorted && samtools index "+ name+"_sorted.bam "
}
"""
$runSamtools
java -Xmx1500m  -jar ${params.igvtools_path} count -w ${igv_window_size} -e ${igv_extention_factor} ${pairedText} ${name}_sorted.bam ${name}.tdf ${params.genome}
"""
}

params.run_Split_Fastq = "no" //* @dropdown @options:"yes","no" @show_settings:"SplitFastq"
readsPerFile = 5000000 //* @input @description:"The number of reads per file"
//Since splitFastq operator requires flat file structure, first convert grouped structure to flat, execute splitFastq, and then return back to original grouped structure
//.map(flatPairsClosure).splitFastq(splitFastqParams).map(groupPairsClosure)

//Mapping grouped read structure to flat structure
flatPairsClosure = {row -> if(row[1] instanceof Collection) {
        if (row[1][1]){
            tuple(row[0], file(row[1][0]), file(row[1][1]))
        } else {
            tuple(row[0], file(row[1][0]))
        }
    } else {
        tuple(row[0], file(row[1]))
    }
}

//Mapping flat read structure to grouped read structure
groupPairsClosure = {row -> tuple(row[0], (row[2]) ? [file(row[1]), file(row[2])] : [file(row[1])])}

// if mate of split process different than rest of the pipeline, use "mate_split" as input parameter. Otherwise use default "mate" as input parameter
mateParamName = (params.mate_split) ? "mate_split" : "mate"
splitFastqParams = ""
if (params[mateParamName] != "pair"){
    splitFastqParams = [by: readsPerFile, file:true]
}else {
    splitFastqParams = [by: readsPerFile, pe:true, file:true]
}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
if (!(params.run_Split_Fastq == "yes")){
g195_25_reads_g_127.into{g_127_reads_g126_5; g_127_reads_g163_3; g_127_reads_g164_0}
} else {

process SplitFastq {

input:
 val mate from g_204_mate_g_127
 set val(name), file(reads) from g195_25_reads_g_127.map(flatPairsClosure).splitFastq(splitFastqParams).map(groupPairsClosure)

output:
 set val(name), file("split/*")  into g_127_reads_g126_5, g_127_reads_g163_3, g_127_reads_g164_0

when:
params.run_Split_Fastq == "yes"

script:
"""    
mkdir -p split
mv ${reads} split/.
"""
}
}


params_tophat = "-p 4" //* @input @description:"Specify Tophat input parameters"
params.genomeIndexPath = "" //* @input
params.gtfFilePath = "" //* @input
params.tophat2_path = "" //* @input

//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 3
    $MEMORY = 24
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 2500
    $CPU  = 4
    $MEMORY = 24
    $QUEUE = "long"
}
//* platform
//* autofill
process Tophat2_Module_Map_Tophat2 {

input:
 val mate from g_204_mate_g164_0
 set val(name), file(reads) from g_127_reads_g164_0

output:
 set val(name), file("${newName}.bam")  into g164_0_mapped_reads_g164_4
 set val(name), file("${newName}_unmapped.bam")  into g164_0_unmapped_reads
 set val(name), file("${newName}_align_summary.txt")  into g164_0_summary_g164_3, g164_0_summary_g_177

when:
(params.run_Tophat && (params.run_Tophat == "yes")) || !params.run_Tophat

script:
nameAll = reads.toString()
nameArray = nameAll.split(' ')

if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file =  nameAll - '.gz' - '.gz'
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file =  nameAll 
    runGzip = ''
}

"""
$runGzip
if [ "${mate}" == "pair" ]; then
    tophat2 ${params_tophat}  --keep-tmp -G ${params.gtfFilePath} -o . ${params.genomeIndexPath} $file
else
    tophat2 ${params_tophat}  --keep-tmp -G ${params.gtfFilePath} -o . ${params.genomeIndexPath} $file
fi

if [ -f unmapped.bam ]; then
    mv unmapped.bam ${newName}_unmapped.bam
else
    touch ${newName}_unmapped.bam
fi

mv accepted_hits.bam ${newName}.bam
mv align_summary.txt ${newName}_align_summary.txt
"""
}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
process Tophat2_Module_Merge_Tophat_Summary {

input:
 set val(name), file(alignSum) from g164_0_summary_g164_3.groupTuple()
 val mate from g_204_mate_g164_3

output:
 set val(name), file("${name}_tophat_sum.tsv")  into g164_3_report_g164_9
 val "tophat2_alignment_sum"  into g164_3_name_g164_9

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

my %tsv;
my @headers = ();
my $name = "!{name}";

alteredAligned();

my @keys = keys %tsv;
my $summary = "$name"."_tophat_sum.tsv";
my $header_string = join("\\t", @headers);
`echo "$header_string" > $summary`;
foreach my $key (@keys){
	my $values = join("\\t", @{ $tsv{$key} });
	`echo "$values" >> $summary`;
}


sub alteredAligned
{
	my @files = qw(!{alignSum});
	my $multimappedSum;
	my $alignedSum;
	my $inputCountSum;
	push(@headers, "Sample");
    push(@headers, "Total Reads");
	push(@headers, "Multimapped Reads Aligned (Tophat2)");
	push(@headers, "Unique Reads Aligned (Tophat2)");
	foreach my $file (@files){
		my $multimapped;
		my $aligned;
		my $inputCount;
		chomp($aligned = `cat $file | grep 'Aligned pairs:' | awk '{sum=\\$3} END {print sum}'`);
		if ($aligned eq "") { # then it is single-end
		        chomp($inputCount = `cat $file | grep 'Input' | awk '{sum=\\$3} END {print sum}'`);
				chomp($aligned = `cat $file | grep 'Mapped' | awk '{sum=\\$3} END {print sum}'`);
				chomp($multimapped = `cat $file | grep 'multiple alignments' | awk '{sum+=\\$3} END {print sum}'`);
			}else{ # continue to pair end
			    chomp($inputCount = `cat $file | grep 'Input' | awk '{sum=\\$3} END {print sum}'`);
				chomp($multimapped = `cat $file | grep -A 1 'Aligned pairs:' | awk 'NR % 3 == 2 {sum+=\\$3} END {print sum}'`);
			}
        $multimappedSum += int($multimapped);
        $alignedSum += (int($aligned) - int($multimapped));
        $inputCountSum += int($inputCount);
        if ($alignedSum < 0){
            $alignedSum = 0;
        }
	}
	$tsv{$name} = [$name, $inputCountSum];
	push($tsv{$name}, $multimappedSum);
	push($tsv{$name}, $alignedSum);
}
'''

}


process Tophat2_Module_Merge_TSV_Files {

input:
 file tsv from g164_3_report_g164_9.collect()
 val outputFileName from g164_3_name_g164_9.collect()

output:
 file "${name}.tsv"  into g164_9_outputFileTSV_g_198

script:
name = outputFileName[0]
"""    
awk 'FNR==1 && NR!=1 {  getline; } 1 {print} ' *.tsv > ${name}.tsv
"""
}

params.samtools_path = "" //* @input

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
process Tophat2_Module_Merge_Bam {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_sorted.*bai$/) "tophat2/$filename"
	else if (filename =~ /.*_sorted.*bam$/) "tophat2/$filename"
}

input:
 set val(oldname), file(bamfiles) from g164_0_mapped_reads_g164_4.groupTuple()

output:
 set val(oldname), file("${oldname}.bam")  into g164_4_merged_bams
 set val(oldname), file("*_sorted*bai")  into g164_4_bam_index
 set val(oldname), file("*_sorted*bam")  into g164_4_sorted_bam_g190_109, g164_4_sorted_bam_g190_110, g164_4_sorted_bam_g190_111, g164_4_sorted_bam_g190_112, g164_4_sorted_bam_g190_120

shell:
'''
num=$(echo "!{bamfiles.join(" ")}" | awk -F" " '{print NF-1}')
if [ "${num}" -gt 0 ]; then
    !{params.samtools_path} merge !{oldname}.bam !{bamfiles.join(" ")} && !{params.samtools_path} sort -O bam -T !{oldname} -o !{oldname}_sorted.bam !{oldname}.bam && !{params.samtools_path} index !{oldname}_sorted.bam
else
    mv !{bamfiles.join(" ")} !{oldname}.bam 2>/dev/null || true
    !{params.samtools_path} sort  -T !{oldname} -O bam -o !{oldname}_sorted.bam !{oldname}.bam && !{params.samtools_path} index !{oldname}_sorted.bam
fi
'''
}

params.gtfFilePath = "" //* @input
params.featureCounts_path = "" //* @input
process BAM_Analysis_Tophat2_featureCounts {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*$/) "featureCounts_after_Tophat2/$filename"
}

input:
 set val(name), file(bam) from g164_4_sorted_bam_g190_120
 val paired from g_204_mate_g190_120
 each run_params from g190_113_run_parameters_g190_120

output:
 file "*"  into g190_120_outputFileTSV_g190_117

script:
pairText = ""
if (paired == "pair"){
    pairText = "-p"
}

run_name = run_params["run_name"] 
run_parameters = run_params["run_parameters"] 

"""
${params.featureCounts_path} ${pairText} ${run_parameters} -a ${params.gtfFilePath} -o ${name}@${run_name}@fCounts.txt ${bam}
## remove first line
sed -i '1d' ${name}@${run_name}@fCounts.txt

"""
}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 30
    $CPU  = 1
    $MEMORY = 10
    $QUEUE = "short"
}
//* platform
//* autofill
process BAM_Analysis_Tophat2_summary_featureCounts {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_featureCounts.tsv$/) "featureCounts_after_Tophat2_summary/$filename"
	else if (filename =~ /.*_featureCounts.sum.tsv$/) "featureCounts_after_Tophat2_details/$filename"
}

input:
 file featureCountsOut from g190_120_outputFileTSV_g190_117.collect()

output:
 file "*_featureCounts.tsv"  into g190_117_outputFile
 file "*_featureCounts.sum.tsv"  into g190_117_outFileTSV

shell:
'''
#!/usr/bin/env perl

# Step 1: Merge count files
my %tf = ( expected_count => 6 );
my @run_name=();
chomp(my $contents = `ls *@fCounts.txt`);
my @files = split(/[\\n]+/, $contents);
foreach my $file (@files){
    $file=~/(.*)\\@(.*)\\@fCounts\\.txt/;
    my $runname = $2;
    push(@run_name, $runname) unless grep{$_ eq $runname} @run_name;
}


my @expectedCount_ar = ("expected_count");
for($l = 0; $l <= $#run_name; $l++) {
    my $runName = $run_name[$l];
    for($ll = 0; $ll <= $#expectedCount_ar; $ll++) {
        my $expectedCount = $expectedCount_ar[$ll];
    
        my @a=();
        my %b=();
        my %c=();
        my $i=0;
        chomp(my $contents = `ls *\\@${runName}\\@fCounts.txt`);
        my @files = split(/[\\n]+/, $contents);
        foreach my $file (@files){
        $i++;
        $file=~/(.*)\\@${runName}\\@fCounts\\.txt/;
        my $libname = $1; 
        $a[$i]=$libname;
        open IN, $file;
            $_=<IN>;
            while(<IN>){
                my @v=split; 
                $b{$v[0]}{$i}=$v[$tf{$expectedCount}];
                $c{$v[0]}=$v[5]; #length column
            }
            close IN;
        }
        my $outfile="$runName"."_featureCounts.tsv";
        open OUT, ">$outfile";
        if ($runName eq "transcript_id") {
            print OUT "transcript\tlength";
        } else {
            print OUT "gene\tlength";
        }
    
        for(my $j=1;$j<=$i;$j++) {
            print OUT "\t$a[$j]";
        }
        print OUT "\n";
    
        foreach my $key (keys %b) {
            print OUT "$key\t$c{$key}";
            for(my $j=1;$j<=$i;$j++){
                print OUT "\t$b{$key}{$j}";
            }
            print OUT "\n";
        }
        close OUT;
    }
}

# Step 2: Merge summary files
for($l = 0; $l <= $#run_name; $l++) {
    my $runName = $run_name[$l];
    my @a=();
    my %b=();
    my $i=0;
    chomp(my $contents = `ls *\\@${runName}\\@fCounts.txt.summary`);
    my @files = split(/[\\n]+/, $contents);
    foreach my $file (@files){
        $i++;
        $file=~/(.*)\\@${runName}\\@fCounts\\.txt\\.summary/;
        my $libname = $1; 
        $a[$i]=$libname;
        open IN, $file;
        $_=<IN>;
        while(<IN>){
            my @v=split; 
            $b{$v[0]}{$i}=$v[1];
        }
        close IN;
    }
    my $outfile="$runName"."_featureCounts.sum.tsv";
    open OUT, ">$outfile";
    print OUT "criteria";
    for(my $j=1;$j<=$i;$j++) {
        print OUT "\t$a[$j]";
    }
    print OUT "\n";
    
    foreach my $key (keys %b) {
        print OUT "$key";
        for(my $j=1;$j<=$i;$j++){
            print OUT "\t$b{$key}{$j}";
        }
        print OUT "\n";
    }
    close OUT;
}

'''
}

params.genomeCoverageBed_path = "" //* @input
params.wigToBigWig_path = "" //* @input
params.genomeSizePath = "" //* @input
process BAM_Analysis_Tophat2_UCSC_BAM2BigWig_converter {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.bw$/) "bigwig_tophat/$filename"
}

input:
 set val(name), file(bam) from g164_4_sorted_bam_g190_112

output:
 file "*.bw"  into g190_112_outputFileBw

when:
(params.run_BigWig_Conversion && (params.run_BigWig_Conversion == "yes")) || !params.run_BigWig_Conversion

script:
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = ''
} else {
    runSamtools = "samtools sort " +bam+ " "+ name +"_sorted && samtools index "+ name+"_sorted.bam "
}
"""
$runSamtools
${params.genomeCoverageBed_path} -split -bg -ibam ${name}_sorted.bam -g ${params.genomeSizePath} > ${name}.bg 
${params.wigToBigWig_path} -clip -itemsPerSlot=1 ${name}.bg ${params.genomeSizePath} ${name}.bw 
"""
}

igv_extention_factor = "0" //* @input @description:"The read or feature is extended by the specified distance in bp prior to counting. This option is useful for chip-seq and rna-seq applications. The value is generally set to the average fragment length of the library minus the average read length." @tooltip:"igvtools is used"
igv_window_size = "5" //* @input  @description:"The window size over which coverage is averaged." @tooltip:"igvtools is used"

params.genome = "" //* @input
params.igvtools_path = "" //* @input
process BAM_Analysis_Tophat2_IGV_BAM2TDF_converter {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tdf$/) "igvtools_tophat2/$filename"
}

input:
 val mate from g_204_mate_g190_111
 set val(name), file(bam) from g164_4_sorted_bam_g190_111

output:
 file "*.tdf"  into g190_111_outputFileOut

when:
(params.run_IGV_TDF_Conversion && (params.run_IGV_TDF_Conversion == "yes")) || !params.run_IGV_TDF_Conversion

script:
pairedText = (params.nucleicAcidType == "dna" && mate == "pair") ? " --pairs " : ""
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = ''
} else {
    runSamtools = "samtools sort " +bam+ " "+ name +"_sorted && samtools index "+ name+"_sorted.bam "
}
"""
$runSamtools
java -Xmx1500m  -jar ${params.igvtools_path} count -w ${igv_window_size} -e ${igv_extention_factor} ${pairedText} ${name}_sorted.bam ${name}.tdf ${params.genome}
"""
}

params.bed_file_genome = "" //* @input
process BAM_Analysis_Tophat2_RSeQC {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /result\/.*.out$/) "rseqc_tophat2/$filename"
}

input:
 set val(name), file(bam) from g164_4_sorted_bam_g190_110

output:
 file "result/*.out"  into g190_110_outputFileOut_g190_95, g190_110_outputFileOut_g_177

when:
(params.run_RSeQC && (params.run_RSeQC == "yes")) || !params.run_RSeQC

script:
"""
mkdir result
read_distribution.py  -i ${bam} -r ${params.bed_file_genome}> result/RSeQC.${name}.out
"""
}


process BAM_Analysis_Tophat2_RSeQC_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tsv$/) "rseqc_summary_tophat2/$filename"
}

input:
 file rseqcOut from g190_110_outputFileOut_g190_95.collect()
 val mate from g_204_mate_g190_95

output:
 file "*.tsv"  into g190_95_outputFileTSV

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

my $indir = $ENV{'PWD'};
my $outd = $ENV{'PWD'};
my @files = ();
my @outtypes = ("RSeQC");
my @order=( "Total Reads", "Total Tags" , "Total Assigned Tags", "CDS_Exons", "5'UTR_Exons", "3'UTR_Exons", "Introns", "TSS_up_1kb", "TSS_up_5kb", "TSS_up_10kb", "TES_down_1kb", "TES_down_5kb", "TES_down_10kb");
my %lines=(
  "Total Reads" => 1,
  "Total Tags" => 1,
  "Total Assigned Tags" => 1,
  "CDS_Exons" => 2,
  "5'UTR_Exons" => 2,
  "3'UTR_Exons" => 2,
  "Introns" => 2,
  "TSS_up_1kb" => 2,
  "TSS_up_5kb" => 2,
  "TSS_up_10kb" => 2,
  "TES_down_1kb" => 2,
  "TES_down_5kb" => 2,
  "TES_down_10kb" => 2
);


foreach my $outtype (@outtypes)
{

my $ext=".out";
@files = <$indir/$outtype*$ext>;

my @rowheaders=();
my @libs=();
my %vals=();
my %normvals=();
my $type = "rsem";

foreach my $d (@files){
  my $libname=basename($d, $ext);
  $libname=~s/RSeQC.//g;
  $libname=~s/rsem.out.//g;
  $libname=~s/.genome//g;
  print $libname."\\n";
  push(@libs, $libname); 
  getVals($d, $libname, \\%vals, \\%normvals, \\%lines);
}
#print Dumper(%vals);
#print Dumper(%normvals);

my $sizemetrics = keys %vals;
write_results("$outd/$outtype.$type.counts.tsv", \\@libs,\\%vals, \\@order, "region") if ($sizemetrics>0);
write_results("$outd/$outtype.$type.tagskb.tsv", \\@libs,\\%normvals, \\@order, "region") if ($sizemetrics>0);

}

sub write_results
{
  my ($outfile, $libs, $vals, $order, $name )=@_;
  open(OUT, ">$outfile");
  print OUT "$name\\t".join("\\t", @{$libs})."\\n";

  my $lib=${$libs}[0];
  foreach my $key ( @order )
  {
    if (exists ${$vals}{$lib}{$key}) {
    print OUT $key;
    foreach my $lib (@{$libs})
    {
      print OUT "\\t".${$vals}{$lib}{$key};
    } 
    print OUT "\\n";
    }
  }
  close(OUT);
}

sub getVals{
  my ($filename, $libname, $vals, $normvals, $lines)=@_;
  if (-e $filename){
     open(IN, $filename);
     while(my $line=<IN>)
     {
       chomp($line);
       my @vals_arr=split(/\\s{2,}/,$line);
       if (exists ${$lines}{$vals_arr[0]}) {
         my $idx=${$lines}{$vals_arr[0]};
         ${$vals}{$libname}{$vals_arr[0]}=$vals_arr[$idx] if (exists $vals_arr[$idx]);
         if ($idx==2) {
             ${$normvals}{$libname}{$vals_arr[0]}=$vals_arr[3] if (exists $vals_arr[3]);
         }
       }
     } 
  }
  
}
'''

}

params.pdfbox_path = "" //* @input
params.picard_path = "" //* @input
//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 32
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 32
    $QUEUE = "short"
}
//* platform
//* autofill
process BAM_Analysis_Tophat2_Picard {

input:
 set val(name), file(bam) from g164_4_sorted_bam_g190_109

output:
 file "*_metrics"  into g190_109_outputFileOut_g190_82
 file "results/*.pdf"  into g190_109_outputFilePdf_g190_82

when:
(params.run_Picard_CollectMultipleMetrics && (params.run_Picard_CollectMultipleMetrics == "yes")) || !params.run_Picard_CollectMultipleMetrics

script:
"""
java -jar ${params.picard_path} CollectMultipleMetrics OUTPUT=${name}_multiple.out VALIDATION_STRINGENCY=LENIENT INPUT=${bam}
mkdir results && java -jar ${params.pdfbox_path} PDFMerger *.pdf results/${name}_multi_metrics.pdf
"""
}


process BAM_Analysis_Tophat2_Picard_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tsv$/) "picard_summary_tophat2/$filename"
	else if (filename =~ /results\/.*.pdf$/) "picard_summary_pdf_tophat2/$filename"
}

input:
 file picardOut from g190_109_outputFileOut_g190_82.collect()
 val mate from g_204_mate_g190_82
 file picardPdf from g190_109_outputFilePdf_g190_82.collect()

output:
 file "*.tsv"  into g190_82_outputFileTSV
 file "results/*.pdf"  into g190_82_outputFilePdf

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

system("mkdir results && mv *.pdf results/. ");

my $indir = $ENV{'PWD'};
my $outd = $ENV{'PWD'};
my @files = ();
my @outtypes = ("CollectRnaSeqMetrics", "alignment_summary_metrics", "base_distribution_by_cycle_metrics", "insert_size_metrics", "quality_by_cycle_metrics", "quality_distribution_metrics" );

foreach my $outtype (@outtypes)
{
my $ext="_multiple.out";
$ext.=".$outtype" if ($outtype ne "CollectRnaSeqMetrics");
@files = <$indir/*$ext>;

my @rowheaders=();
my @libs=();
my %metricvals=();
my %histvals=();

my $pdffile="";
my $libname="";
foreach my $d (@files){
  my $libname=basename($d, $ext);
  print $libname."\\n";
  push(@libs, $libname); 
  getMetricVals($d, $libname, \\%metricvals, \\%histvals, \\@rowheaders);
}

my $sizemetrics = keys %metricvals;
write_results("$outd/$outtype.stats.tsv", \\@libs,\\%metricvals, \\@rowheaders, "metric") if ($sizemetrics>0);
my $sizehist = keys %histvals;
write_results("$outd/$outtype.hist.tsv", \\@libs,\\%histvals, "none", "nt") if ($sizehist>0);

}

sub write_results
{
  my ($outfile, $libs, $vals, $rowheaders, $name )=@_;
  open(OUT, ">$outfile");
  print OUT "$name\\t".join("\\t", @{$libs})."\\n";
  my $size=0;
  $size=scalar(@{${$vals}{${$libs}[0]}}) if(exists ${$libs}[0] and exists ${$vals}{${$libs}[0]} );
  
  for (my $i=0; $i<$size;$i++)
  { 
    my $rowname=$i;
    $rowname = ${$rowheaders}[$i] if ($name=~/metric/);
    print OUT $rowname;
    foreach my $lib (@{$libs})
    {
      print OUT "\\t".${${$vals}{$lib}}[$i];
    } 
    print OUT "\\n";
  }
  close(OUT);
}

sub getMetricVals{
  my ($filename, $libname, $metricvals, $histvals,$rowheaders)=@_;
  if (-e $filename){
     my $nextisheader=0;
     my $nextisvals=0;
     my $nexthist=0;
     open(IN, $filename);
     while(my $line=<IN>)
     {
       chomp($line);
       @{$rowheaders}=split(/\\t/, $line) if ($nextisheader && !scalar(@{$rowheaders})); 
       if ($nextisvals) {
         @{${$metricvals}{$libname}}=split(/\\t/, $line);
         $nextisvals=0;
       }
       if($nexthist){
          my @vals=split(/[\\s\\t]+/,$line); 
          push(@{${$histvals}{$libname}}, $vals[1]) if (exists $vals[1]);
       }
       $nextisvals=1 if ($nextisheader); $nextisheader=0;
       $nextisheader=1 if ($line=~/METRICS CLASS/);
       $nexthist=1 if ($line=~/normalized_position/);
     } 
  }
  
}
'''

}

HISAT2_parameters = "-p 4" //* @input @description:"Specify HISAT2 input parameters"
params.genomeIndexPath = "" //* @input
params.hisat2_path = "" //* @input

//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 3
    $MEMORY = 32
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 100
    $CPU  = 4
    $MEMORY = 32
    $QUEUE = "short"
} 
//* platform
//* autofill
process HISAT2_Module_Map_HISAT2 {

input:
 val mate from g_204_mate_g163_3
 set val(name), file(reads) from g_127_reads_g163_3

output:
 set val(name), file("${newName}.bam")  into g163_3_mapped_reads_g163_1
 set val(name), file("${newName}.align_summary.txt")  into g163_3_outputFileTxt_g163_2
 set val(name), file("${newName}.flagstat.txt")  into g163_3_outputFileOut

when:
(params.run_HISAT2 && (params.run_HISAT2 == "yes")) || !params.run_HISAT2

script:
nameAll = reads.toString()
nameArray = nameAll.split(' ')
def file2;

if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file1 =  nameArray[0] - '.gz' 
    if (mate == "pair") {file2 =  nameArray[1] - '.gz'}
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file1 =  nameArray[0]
    if (mate == "pair") {file2 =  nameArray[1]}
    runGzip = ''
}

"""
$runGzip
if [ "${mate}" == "pair" ]; then
    ${params.hisat2_path} ${HISAT2_parameters} -x ${params.genomeIndexPath} -1 ${file1} -2 ${file2} -S ${newName}.sam &> ${newName}.align_summary.txt
else
    ${params.hisat2_path} ${HISAT2_parameters} -x ${params.genomeIndexPath} -U ${file1} -S ${newName}.sam &> ${newName}.align_summary.txt
fi
samtools view -bS ${newName}.sam > ${newName}.bam
samtools flagstat ${newName}.bam > ${newName}.flagstat.txt
"""
}

params.samtools_path = "" //* @input

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
process HISAT2_Module_Merge_Bam {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_sorted.*bam$/) "hisat2/$filename"
}

input:
 set val(oldname), file(bamfiles) from g163_3_mapped_reads_g163_1.groupTuple()

output:
 set val(oldname), file("${oldname}.bam")  into g163_1_merged_bams
 set val(oldname), file("*_sorted*bai")  into g163_1_bam_index
 set val(oldname), file("*_sorted*bam")  into g163_1_sorted_bam_g184_109, g163_1_sorted_bam_g184_110, g163_1_sorted_bam_g184_111, g163_1_sorted_bam_g184_112, g163_1_sorted_bam_g184_120

shell:
'''
num=$(echo "!{bamfiles.join(" ")}" | awk -F" " '{print NF-1}')
if [ "${num}" -gt 0 ]; then
    !{params.samtools_path} merge !{oldname}.bam !{bamfiles.join(" ")} && !{params.samtools_path} sort -O bam -T !{oldname} -o !{oldname}_sorted.bam !{oldname}.bam && !{params.samtools_path} index !{oldname}_sorted.bam
else
    mv !{bamfiles.join(" ")} !{oldname}.bam 2>/dev/null || true
    !{params.samtools_path} sort  -T !{oldname} -O bam -o !{oldname}_sorted.bam !{oldname}.bam && !{params.samtools_path} index !{oldname}_sorted.bam
fi
'''
}

params.gtfFilePath = "" //* @input
params.featureCounts_path = "" //* @input
process BAM_Analysis_Hisat2_featureCounts {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*$/) "featureCounts_after_hisat2/$filename"
}

input:
 set val(name), file(bam) from g163_1_sorted_bam_g184_120
 val paired from g_204_mate_g184_120
 each run_params from g184_113_run_parameters_g184_120

output:
 file "*"  into g184_120_outputFileTSV_g184_117

script:
pairText = ""
if (paired == "pair"){
    pairText = "-p"
}

run_name = run_params["run_name"] 
run_parameters = run_params["run_parameters"] 

"""
${params.featureCounts_path} ${pairText} ${run_parameters} -a ${params.gtfFilePath} -o ${name}@${run_name}@fCounts.txt ${bam}
## remove first line
sed -i '1d' ${name}@${run_name}@fCounts.txt

"""
}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 30
    $CPU  = 1
    $MEMORY = 10
    $QUEUE = "short"
}
//* platform
//* autofill
process BAM_Analysis_Hisat2_summary_featureCounts {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_featureCounts.tsv$/) "featureCounts_after_hisat2_summary/$filename"
	else if (filename =~ /.*_featureCounts.sum.tsv$/) "featureCounts_after_hisat2_details/$filename"
}

input:
 file featureCountsOut from g184_120_outputFileTSV_g184_117.collect()

output:
 file "*_featureCounts.tsv"  into g184_117_outputFile
 file "*_featureCounts.sum.tsv"  into g184_117_outFileTSV

shell:
'''
#!/usr/bin/env perl

# Step 1: Merge count files
my %tf = ( expected_count => 6 );
my @run_name=();
chomp(my $contents = `ls *@fCounts.txt`);
my @files = split(/[\\n]+/, $contents);
foreach my $file (@files){
    $file=~/(.*)\\@(.*)\\@fCounts\\.txt/;
    my $runname = $2;
    push(@run_name, $runname) unless grep{$_ eq $runname} @run_name;
}


my @expectedCount_ar = ("expected_count");
for($l = 0; $l <= $#run_name; $l++) {
    my $runName = $run_name[$l];
    for($ll = 0; $ll <= $#expectedCount_ar; $ll++) {
        my $expectedCount = $expectedCount_ar[$ll];
    
        my @a=();
        my %b=();
        my %c=();
        my $i=0;
        chomp(my $contents = `ls *\\@${runName}\\@fCounts.txt`);
        my @files = split(/[\\n]+/, $contents);
        foreach my $file (@files){
        $i++;
        $file=~/(.*)\\@${runName}\\@fCounts\\.txt/;
        my $libname = $1; 
        $a[$i]=$libname;
        open IN, $file;
            $_=<IN>;
            while(<IN>){
                my @v=split; 
                $b{$v[0]}{$i}=$v[$tf{$expectedCount}];
                $c{$v[0]}=$v[5]; #length column
            }
            close IN;
        }
        my $outfile="$runName"."_featureCounts.tsv";
        open OUT, ">$outfile";
        if ($runName eq "transcript_id") {
            print OUT "transcript\tlength";
        } else {
            print OUT "gene\tlength";
        }
    
        for(my $j=1;$j<=$i;$j++) {
            print OUT "\t$a[$j]";
        }
        print OUT "\n";
    
        foreach my $key (keys %b) {
            print OUT "$key\t$c{$key}";
            for(my $j=1;$j<=$i;$j++){
                print OUT "\t$b{$key}{$j}";
            }
            print OUT "\n";
        }
        close OUT;
    }
}

# Step 2: Merge summary files
for($l = 0; $l <= $#run_name; $l++) {
    my $runName = $run_name[$l];
    my @a=();
    my %b=();
    my $i=0;
    chomp(my $contents = `ls *\\@${runName}\\@fCounts.txt.summary`);
    my @files = split(/[\\n]+/, $contents);
    foreach my $file (@files){
        $i++;
        $file=~/(.*)\\@${runName}\\@fCounts\\.txt\\.summary/;
        my $libname = $1; 
        $a[$i]=$libname;
        open IN, $file;
        $_=<IN>;
        while(<IN>){
            my @v=split; 
            $b{$v[0]}{$i}=$v[1];
        }
        close IN;
    }
    my $outfile="$runName"."_featureCounts.sum.tsv";
    open OUT, ">$outfile";
    print OUT "criteria";
    for(my $j=1;$j<=$i;$j++) {
        print OUT "\t$a[$j]";
    }
    print OUT "\n";
    
    foreach my $key (keys %b) {
        print OUT "$key";
        for(my $j=1;$j<=$i;$j++){
            print OUT "\t$b{$key}{$j}";
        }
        print OUT "\n";
    }
    close OUT;
}

'''
}

params.genomeCoverageBed_path = "" //* @input
params.wigToBigWig_path = "" //* @input
params.genomeSizePath = "" //* @input
process BAM_Analysis_Hisat2_UCSC_BAM2BigWig_converter {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.bw$/) "bigwig_hisat2/$filename"
}

input:
 set val(name), file(bam) from g163_1_sorted_bam_g184_112

output:
 file "*.bw"  into g184_112_outputFileBw

when:
(params.run_BigWig_Conversion && (params.run_BigWig_Conversion == "yes")) || !params.run_BigWig_Conversion

script:
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = ''
} else {
    runSamtools = "samtools sort " +bam+ " "+ name +"_sorted && samtools index "+ name+"_sorted.bam "
}
"""
$runSamtools
${params.genomeCoverageBed_path} -split -bg -ibam ${name}_sorted.bam -g ${params.genomeSizePath} > ${name}.bg 
${params.wigToBigWig_path} -clip -itemsPerSlot=1 ${name}.bg ${params.genomeSizePath} ${name}.bw 
"""
}

igv_extention_factor = "0" //* @input @description:"The read or feature is extended by the specified distance in bp prior to counting. This option is useful for chip-seq and rna-seq applications. The value is generally set to the average fragment length of the library minus the average read length." @tooltip:"igvtools is used"
igv_window_size = "5" //* @input  @description:"The window size over which coverage is averaged." @tooltip:"igvtools is used"

params.genome = "" //* @input
params.igvtools_path = "" //* @input
process BAM_Analysis_Hisat2_IGV_BAM2TDF_converter {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tdf$/) "igvtools_hisat2/$filename"
}

input:
 val mate from g_204_mate_g184_111
 set val(name), file(bam) from g163_1_sorted_bam_g184_111

output:
 file "*.tdf"  into g184_111_outputFileOut

when:
(params.run_IGV_TDF_Conversion && (params.run_IGV_TDF_Conversion == "yes")) || !params.run_IGV_TDF_Conversion

script:
pairedText = (params.nucleicAcidType == "dna" && mate == "pair") ? " --pairs " : ""
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = ''
} else {
    runSamtools = "samtools sort " +bam+ " "+ name +"_sorted && samtools index "+ name+"_sorted.bam "
}
"""
$runSamtools
java -Xmx1500m  -jar ${params.igvtools_path} count -w ${igv_window_size} -e ${igv_extention_factor} ${pairedText} ${name}_sorted.bam ${name}.tdf ${params.genome}
"""
}

params.bed_file_genome = "" //* @input
process BAM_Analysis_Hisat2_RSeQC {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /result\/.*.out$/) "rseqc_hisat2/$filename"
}

input:
 set val(name), file(bam) from g163_1_sorted_bam_g184_110

output:
 file "result/*.out"  into g184_110_outputFileOut_g184_95, g184_110_outputFileOut_g_177

when:
(params.run_RSeQC && (params.run_RSeQC == "yes")) || !params.run_RSeQC

script:
"""
mkdir result
read_distribution.py  -i ${bam} -r ${params.bed_file_genome}> result/RSeQC.${name}.out
"""
}


process BAM_Analysis_Hisat2_RSeQC_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tsv$/) "rseqc_summary_hisat2/$filename"
}

input:
 file rseqcOut from g184_110_outputFileOut_g184_95.collect()
 val mate from g_204_mate_g184_95

output:
 file "*.tsv"  into g184_95_outputFileTSV

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

my $indir = $ENV{'PWD'};
my $outd = $ENV{'PWD'};
my @files = ();
my @outtypes = ("RSeQC");
my @order=( "Total Reads", "Total Tags" , "Total Assigned Tags", "CDS_Exons", "5'UTR_Exons", "3'UTR_Exons", "Introns", "TSS_up_1kb", "TSS_up_5kb", "TSS_up_10kb", "TES_down_1kb", "TES_down_5kb", "TES_down_10kb");
my %lines=(
  "Total Reads" => 1,
  "Total Tags" => 1,
  "Total Assigned Tags" => 1,
  "CDS_Exons" => 2,
  "5'UTR_Exons" => 2,
  "3'UTR_Exons" => 2,
  "Introns" => 2,
  "TSS_up_1kb" => 2,
  "TSS_up_5kb" => 2,
  "TSS_up_10kb" => 2,
  "TES_down_1kb" => 2,
  "TES_down_5kb" => 2,
  "TES_down_10kb" => 2
);


foreach my $outtype (@outtypes)
{

my $ext=".out";
@files = <$indir/$outtype*$ext>;

my @rowheaders=();
my @libs=();
my %vals=();
my %normvals=();
my $type = "rsem";

foreach my $d (@files){
  my $libname=basename($d, $ext);
  $libname=~s/RSeQC.//g;
  $libname=~s/rsem.out.//g;
  $libname=~s/.genome//g;
  print $libname."\\n";
  push(@libs, $libname); 
  getVals($d, $libname, \\%vals, \\%normvals, \\%lines);
}
#print Dumper(%vals);
#print Dumper(%normvals);

my $sizemetrics = keys %vals;
write_results("$outd/$outtype.$type.counts.tsv", \\@libs,\\%vals, \\@order, "region") if ($sizemetrics>0);
write_results("$outd/$outtype.$type.tagskb.tsv", \\@libs,\\%normvals, \\@order, "region") if ($sizemetrics>0);

}

sub write_results
{
  my ($outfile, $libs, $vals, $order, $name )=@_;
  open(OUT, ">$outfile");
  print OUT "$name\\t".join("\\t", @{$libs})."\\n";

  my $lib=${$libs}[0];
  foreach my $key ( @order )
  {
    if (exists ${$vals}{$lib}{$key}) {
    print OUT $key;
    foreach my $lib (@{$libs})
    {
      print OUT "\\t".${$vals}{$lib}{$key};
    } 
    print OUT "\\n";
    }
  }
  close(OUT);
}

sub getVals{
  my ($filename, $libname, $vals, $normvals, $lines)=@_;
  if (-e $filename){
     open(IN, $filename);
     while(my $line=<IN>)
     {
       chomp($line);
       my @vals_arr=split(/\\s{2,}/,$line);
       if (exists ${$lines}{$vals_arr[0]}) {
         my $idx=${$lines}{$vals_arr[0]};
         ${$vals}{$libname}{$vals_arr[0]}=$vals_arr[$idx] if (exists $vals_arr[$idx]);
         if ($idx==2) {
             ${$normvals}{$libname}{$vals_arr[0]}=$vals_arr[3] if (exists $vals_arr[3]);
         }
       }
     } 
  }
  
}
'''

}

params.pdfbox_path = "" //* @input
params.picard_path = "" //* @input
//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 32
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 32
    $QUEUE = "short"
}
//* platform
//* autofill
process BAM_Analysis_Hisat2_Picard {

input:
 set val(name), file(bam) from g163_1_sorted_bam_g184_109

output:
 file "*_metrics"  into g184_109_outputFileOut_g184_82
 file "results/*.pdf"  into g184_109_outputFilePdf_g184_82

when:
(params.run_Picard_CollectMultipleMetrics && (params.run_Picard_CollectMultipleMetrics == "yes")) || !params.run_Picard_CollectMultipleMetrics

script:
"""
java -jar ${params.picard_path} CollectMultipleMetrics OUTPUT=${name}_multiple.out VALIDATION_STRINGENCY=LENIENT INPUT=${bam}
mkdir results && java -jar ${params.pdfbox_path} PDFMerger *.pdf results/${name}_multi_metrics.pdf
"""
}


process BAM_Analysis_Hisat2_Picard_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tsv$/) "picard_summary_hisat2/$filename"
	else if (filename =~ /results\/.*.pdf$/) "picard_summary_pdf_hisat2/$filename"
}

input:
 file picardOut from g184_109_outputFileOut_g184_82.collect()
 val mate from g_204_mate_g184_82
 file picardPdf from g184_109_outputFilePdf_g184_82.collect()

output:
 file "*.tsv"  into g184_82_outputFileTSV
 file "results/*.pdf"  into g184_82_outputFilePdf

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

system("mkdir results && mv *.pdf results/. ");

my $indir = $ENV{'PWD'};
my $outd = $ENV{'PWD'};
my @files = ();
my @outtypes = ("CollectRnaSeqMetrics", "alignment_summary_metrics", "base_distribution_by_cycle_metrics", "insert_size_metrics", "quality_by_cycle_metrics", "quality_distribution_metrics" );

foreach my $outtype (@outtypes)
{
my $ext="_multiple.out";
$ext.=".$outtype" if ($outtype ne "CollectRnaSeqMetrics");
@files = <$indir/*$ext>;

my @rowheaders=();
my @libs=();
my %metricvals=();
my %histvals=();

my $pdffile="";
my $libname="";
foreach my $d (@files){
  my $libname=basename($d, $ext);
  print $libname."\\n";
  push(@libs, $libname); 
  getMetricVals($d, $libname, \\%metricvals, \\%histvals, \\@rowheaders);
}

my $sizemetrics = keys %metricvals;
write_results("$outd/$outtype.stats.tsv", \\@libs,\\%metricvals, \\@rowheaders, "metric") if ($sizemetrics>0);
my $sizehist = keys %histvals;
write_results("$outd/$outtype.hist.tsv", \\@libs,\\%histvals, "none", "nt") if ($sizehist>0);

}

sub write_results
{
  my ($outfile, $libs, $vals, $rowheaders, $name )=@_;
  open(OUT, ">$outfile");
  print OUT "$name\\t".join("\\t", @{$libs})."\\n";
  my $size=0;
  $size=scalar(@{${$vals}{${$libs}[0]}}) if(exists ${$libs}[0] and exists ${$vals}{${$libs}[0]} );
  
  for (my $i=0; $i<$size;$i++)
  { 
    my $rowname=$i;
    $rowname = ${$rowheaders}[$i] if ($name=~/metric/);
    print OUT $rowname;
    foreach my $lib (@{$libs})
    {
      print OUT "\\t".${${$vals}{$lib}}[$i];
    } 
    print OUT "\\n";
  }
  close(OUT);
}

sub getMetricVals{
  my ($filename, $libname, $metricvals, $histvals,$rowheaders)=@_;
  if (-e $filename){
     my $nextisheader=0;
     my $nextisvals=0;
     my $nexthist=0;
     open(IN, $filename);
     while(my $line=<IN>)
     {
       chomp($line);
       @{$rowheaders}=split(/\\t/, $line) if ($nextisheader && !scalar(@{$rowheaders})); 
       if ($nextisvals) {
         @{${$metricvals}{$libname}}=split(/\\t/, $line);
         $nextisvals=0;
       }
       if($nexthist){
          my @vals=split(/[\\s\\t]+/,$line); 
          push(@{${$histvals}{$libname}}, $vals[1]) if (exists $vals[1]);
       }
       $nextisvals=1 if ($nextisheader); $nextisheader=0;
       $nextisheader=1 if ($line=~/METRICS CLASS/);
       $nexthist=1 if ($line=~/normalized_position/);
     } 
  }
  
}
'''

}


process HISAT2_Module_HISAT2_Summary {

input:
 set val(name), file(alignSum) from g163_3_outputFileTxt_g163_2.groupTuple()

output:
 file "*.tsv"  into g163_2_outputFile_g163_10
 val "hisat2_alignment_sum"  into g163_2_name_g163_10

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

my %tsv;
my @headers = ();
my $name = "!{name}";


alteredAligned();

my @keys = keys %tsv;
my $summary = "$name"."_hisat_sum.tsv";
my $header_string = join("\\t", @headers);
`echo "$header_string" > $summary`;
foreach my $key (@keys){
	my $values = join("\\t", @{ $tsv{$key} });
	`echo "$values" >> $summary`;
}


sub alteredAligned
{
	my @files = qw(!{alignSum});
	my $multimappedSum;
	my $alignedSum;
	my $inputCountSum;
	push(@headers, "Sample");
    push(@headers, "Total Reads");
	push(@headers, "Multimapped Reads Aligned (HISAT2)");
	push(@headers, "Unique Reads Aligned (HISAT2)");
	foreach my $file (@files){
		my $multimapped;
		my $aligned;
		my $inputCount;
		chomp($inputCount = `cat $file | grep 'reads; of these:' | awk '{sum+=\\$1} END {print sum}'`);
		chomp($aligned = `cat $file | grep 'aligned.*exactly 1 time' | awk '{sum+=\\$1} END {print sum}'`);
		chomp($multimapped = `cat $file | grep 'aligned.*>1 times' | awk '{sum+=\\$1} END {print sum}'`);
		$multimappedSum += int($multimapped);
        $alignedSum += int($aligned);
        $inputCountSum += int($inputCount);
	}
	$tsv{$name} = [$name, $inputCountSum];
	push(@{$tsv{$name}}, $multimappedSum);
	push(@{$tsv{$name}}, $alignedSum);
}
'''

}


process HISAT2_Module_Merge_TSV_Files {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /${name}.tsv$/) "hisat2_summary/$filename"
}

input:
 file tsv from g163_2_outputFile_g163_10.collect()
 val outputFileName from g163_2_name_g163_10.collect()

output:
 file "${name}.tsv"  into g163_10_outputFileTSV_g_198

script:
name = outputFileName[0]
"""    
awk 'FNR==1 && NR!=1 {  getline; } 1 {print} ' *.tsv > ${name}.tsv
"""
}

params_STAR = "--runThreadN 4" //* @input @description:"Specify STAR input parameters"
params.star_path = "" //* @input
params.genomeDir = "" //* @input

//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 3
    $MEMORY = 32
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 4
    $MEMORY = 32
    $QUEUE = "long"
} 
//* platform
//* autofill
process STAR_Module_Map_STAR {

input:
 val mate from g_204_mate_g126_5
 set val(name), file(reads) from g_127_reads_g126_5

output:
 set val(name), file("${newName}Log.final.out")  into g126_5_outputFileOut_g126_1
 set val(name), file("${newName}.flagstat.txt")  into g126_5_outputFileTxt
 set val(name), file("${newName}Log.out")  into g126_5_logOut_g126_1
 set val(name), file("${newName}.bam")  into g126_5_mapped_reads_g126_2
 set val(name), file("${newName}SJ.out.tab")  into g126_5_outputFileTab_g126_1
 set val(name), file("${newName}Log.progress.out")  into g126_5_progressOut_g126_1

when:
(params.run_STAR && (params.run_STAR == "yes")) || !params.run_STAR

script:
nameAll = reads.toString()
nameArray = nameAll.split(' ')

if (nameAll.contains('.gz')) {
    newName =  nameArray[0] - ~/(\.fastq.gz)?(\.fq.gz)?$/
    file =  nameAll - '.gz' - '.gz'
    runGzip = "ls *.gz | xargs -i echo gzip -df {} | sh"
} else {
    newName =  nameArray[0] - ~/(\.fastq)?(\.fq)?$/
    file =  nameAll 
    runGzip = ''
}

"""
$runGzip
${params.star_path} ${params_STAR}  --genomeDir ${params.genomeDir} --readFilesIn $file --outSAMtype BAM Unsorted --outFileNamePrefix ${newName}
mv ${newName}Aligned.out.bam ${newName}.bam
samtools flagstat ${newName}.bam > ${newName}.flagstat.txt
"""
}

params.samtools_path = "" //* @input

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
process STAR_Module_Merge_Bam {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_sorted.*bai$/) "star/$filename"
	else if (filename =~ /.*_sorted.*bam$/) "star/$filename"
}

input:
 set val(oldname), file(bamfiles) from g126_5_mapped_reads_g126_2.groupTuple()

output:
 set val(oldname), file("${oldname}.bam")  into g126_2_merged_bams
 set val(oldname), file("*_sorted*bai")  into g126_2_bam_index
 set val(oldname), file("*_sorted*bam")  into g126_2_sorted_bam_g180_109, g126_2_sorted_bam_g180_110, g126_2_sorted_bam_g180_111, g126_2_sorted_bam_g180_112, g126_2_sorted_bam_g180_120

shell:
'''
num=$(echo "!{bamfiles.join(" ")}" | awk -F" " '{print NF-1}')
if [ "${num}" -gt 0 ]; then
    !{params.samtools_path} merge !{oldname}.bam !{bamfiles.join(" ")} && !{params.samtools_path} sort -O bam -T !{oldname} -o !{oldname}_sorted.bam !{oldname}.bam && !{params.samtools_path} index !{oldname}_sorted.bam
else
    mv !{bamfiles.join(" ")} !{oldname}.bam 2>/dev/null || true
    !{params.samtools_path} sort  -T !{oldname} -O bam -o !{oldname}_sorted.bam !{oldname}.bam && !{params.samtools_path} index !{oldname}_sorted.bam
fi
'''
}

params.gtfFilePath = "" //* @input
params.featureCounts_path = "" //* @input
process BAM_Analysis_STAR_featureCounts {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*$/) "featureCounts_after_STAR/$filename"
}

input:
 set val(name), file(bam) from g126_2_sorted_bam_g180_120
 val paired from g_204_mate_g180_120
 each run_params from g180_113_run_parameters_g180_120

output:
 file "*"  into g180_120_outputFileTSV_g180_117

script:
pairText = ""
if (paired == "pair"){
    pairText = "-p"
}

run_name = run_params["run_name"] 
run_parameters = run_params["run_parameters"] 

"""
${params.featureCounts_path} ${pairText} ${run_parameters} -a ${params.gtfFilePath} -o ${name}@${run_name}@fCounts.txt ${bam}
## remove first line
sed -i '1d' ${name}@${run_name}@fCounts.txt

"""
}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 30
    $CPU  = 1
    $MEMORY = 10
    $QUEUE = "short"
}
//* platform
//* autofill
process BAM_Analysis_STAR_summary_featureCounts {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_featureCounts.tsv$/) "featureCounts_after_STAR_summary/$filename"
	else if (filename =~ /.*_featureCounts.sum.tsv$/) "featureCounts_after_STAR_details/$filename"
}

input:
 file featureCountsOut from g180_120_outputFileTSV_g180_117.collect()

output:
 file "*_featureCounts.tsv"  into g180_117_outputFile
 file "*_featureCounts.sum.tsv"  into g180_117_outFileTSV

shell:
'''
#!/usr/bin/env perl

# Step 1: Merge count files
my %tf = ( expected_count => 6 );
my @run_name=();
chomp(my $contents = `ls *@fCounts.txt`);
my @files = split(/[\\n]+/, $contents);
foreach my $file (@files){
    $file=~/(.*)\\@(.*)\\@fCounts\\.txt/;
    my $runname = $2;
    push(@run_name, $runname) unless grep{$_ eq $runname} @run_name;
}


my @expectedCount_ar = ("expected_count");
for($l = 0; $l <= $#run_name; $l++) {
    my $runName = $run_name[$l];
    for($ll = 0; $ll <= $#expectedCount_ar; $ll++) {
        my $expectedCount = $expectedCount_ar[$ll];
    
        my @a=();
        my %b=();
        my %c=();
        my $i=0;
        chomp(my $contents = `ls *\\@${runName}\\@fCounts.txt`);
        my @files = split(/[\\n]+/, $contents);
        foreach my $file (@files){
        $i++;
        $file=~/(.*)\\@${runName}\\@fCounts\\.txt/;
        my $libname = $1; 
        $a[$i]=$libname;
        open IN, $file;
            $_=<IN>;
            while(<IN>){
                my @v=split; 
                $b{$v[0]}{$i}=$v[$tf{$expectedCount}];
                $c{$v[0]}=$v[5]; #length column
            }
            close IN;
        }
        my $outfile="$runName"."_featureCounts.tsv";
        open OUT, ">$outfile";
        if ($runName eq "transcript_id") {
            print OUT "transcript\tlength";
        } else {
            print OUT "gene\tlength";
        }
    
        for(my $j=1;$j<=$i;$j++) {
            print OUT "\t$a[$j]";
        }
        print OUT "\n";
    
        foreach my $key (keys %b) {
            print OUT "$key\t$c{$key}";
            for(my $j=1;$j<=$i;$j++){
                print OUT "\t$b{$key}{$j}";
            }
            print OUT "\n";
        }
        close OUT;
    }
}

# Step 2: Merge summary files
for($l = 0; $l <= $#run_name; $l++) {
    my $runName = $run_name[$l];
    my @a=();
    my %b=();
    my $i=0;
    chomp(my $contents = `ls *\\@${runName}\\@fCounts.txt.summary`);
    my @files = split(/[\\n]+/, $contents);
    foreach my $file (@files){
        $i++;
        $file=~/(.*)\\@${runName}\\@fCounts\\.txt\\.summary/;
        my $libname = $1; 
        $a[$i]=$libname;
        open IN, $file;
        $_=<IN>;
        while(<IN>){
            my @v=split; 
            $b{$v[0]}{$i}=$v[1];
        }
        close IN;
    }
    my $outfile="$runName"."_featureCounts.sum.tsv";
    open OUT, ">$outfile";
    print OUT "criteria";
    for(my $j=1;$j<=$i;$j++) {
        print OUT "\t$a[$j]";
    }
    print OUT "\n";
    
    foreach my $key (keys %b) {
        print OUT "$key";
        for(my $j=1;$j<=$i;$j++){
            print OUT "\t$b{$key}{$j}";
        }
        print OUT "\n";
    }
    close OUT;
}

'''
}

params.genomeCoverageBed_path = "" //* @input
params.wigToBigWig_path = "" //* @input
params.genomeSizePath = "" //* @input
process BAM_Analysis_STAR_UCSC_BAM2BigWig_converter {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.bw$/) "bigwig_star/$filename"
}

input:
 set val(name), file(bam) from g126_2_sorted_bam_g180_112

output:
 file "*.bw"  into g180_112_outputFileBw

when:
(params.run_BigWig_Conversion && (params.run_BigWig_Conversion == "yes")) || !params.run_BigWig_Conversion

script:
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = ''
} else {
    runSamtools = "samtools sort " +bam+ " "+ name +"_sorted && samtools index "+ name+"_sorted.bam "
}
"""
$runSamtools
${params.genomeCoverageBed_path} -split -bg -ibam ${name}_sorted.bam -g ${params.genomeSizePath} > ${name}.bg 
${params.wigToBigWig_path} -clip -itemsPerSlot=1 ${name}.bg ${params.genomeSizePath} ${name}.bw 
"""
}

igv_extention_factor = "0" //* @input @description:"The read or feature is extended by the specified distance in bp prior to counting. This option is useful for chip-seq and rna-seq applications. The value is generally set to the average fragment length of the library minus the average read length." @tooltip:"igvtools is used"
igv_window_size = "5" //* @input  @description:"The window size over which coverage is averaged." @tooltip:"igvtools is used"

params.genome = "" //* @input
params.igvtools_path = "" //* @input
process BAM_Analysis_STAR_IGV_BAM2TDF_converter {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tdf$/) "igvtools_star/$filename"
}

input:
 val mate from g_204_mate_g180_111
 set val(name), file(bam) from g126_2_sorted_bam_g180_111

output:
 file "*.tdf"  into g180_111_outputFileOut

when:
(params.run_IGV_TDF_Conversion && (params.run_IGV_TDF_Conversion == "yes")) || !params.run_IGV_TDF_Conversion

script:
pairedText = (params.nucleicAcidType == "dna" && mate == "pair") ? " --pairs " : ""
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = ''
} else {
    runSamtools = "samtools sort " +bam+ " "+ name +"_sorted && samtools index "+ name+"_sorted.bam "
}
"""
$runSamtools
java -Xmx1500m  -jar ${params.igvtools_path} count -w ${igv_window_size} -e ${igv_extention_factor} ${pairedText} ${name}_sorted.bam ${name}.tdf ${params.genome}
"""
}

params.bed_file_genome = "" //* @input
process BAM_Analysis_STAR_RSeQC {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /result\/.*.out$/) "rseqc_star/$filename"
}

input:
 set val(name), file(bam) from g126_2_sorted_bam_g180_110

output:
 file "result/*.out"  into g180_110_outputFileOut_g180_95, g180_110_outputFileOut_g_177

when:
(params.run_RSeQC && (params.run_RSeQC == "yes")) || !params.run_RSeQC

script:
"""
mkdir result
read_distribution.py  -i ${bam} -r ${params.bed_file_genome}> result/RSeQC.${name}.out
"""
}


process BAM_Analysis_STAR_RSeQC_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tsv$/) "rseqc_summary_star/$filename"
}

input:
 file rseqcOut from g180_110_outputFileOut_g180_95.collect()
 val mate from g_204_mate_g180_95

output:
 file "*.tsv"  into g180_95_outputFileTSV

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

my $indir = $ENV{'PWD'};
my $outd = $ENV{'PWD'};
my @files = ();
my @outtypes = ("RSeQC");
my @order=( "Total Reads", "Total Tags" , "Total Assigned Tags", "CDS_Exons", "5'UTR_Exons", "3'UTR_Exons", "Introns", "TSS_up_1kb", "TSS_up_5kb", "TSS_up_10kb", "TES_down_1kb", "TES_down_5kb", "TES_down_10kb");
my %lines=(
  "Total Reads" => 1,
  "Total Tags" => 1,
  "Total Assigned Tags" => 1,
  "CDS_Exons" => 2,
  "5'UTR_Exons" => 2,
  "3'UTR_Exons" => 2,
  "Introns" => 2,
  "TSS_up_1kb" => 2,
  "TSS_up_5kb" => 2,
  "TSS_up_10kb" => 2,
  "TES_down_1kb" => 2,
  "TES_down_5kb" => 2,
  "TES_down_10kb" => 2
);


foreach my $outtype (@outtypes)
{

my $ext=".out";
@files = <$indir/$outtype*$ext>;

my @rowheaders=();
my @libs=();
my %vals=();
my %normvals=();
my $type = "rsem";

foreach my $d (@files){
  my $libname=basename($d, $ext);
  $libname=~s/RSeQC.//g;
  $libname=~s/rsem.out.//g;
  $libname=~s/.genome//g;
  print $libname."\\n";
  push(@libs, $libname); 
  getVals($d, $libname, \\%vals, \\%normvals, \\%lines);
}
#print Dumper(%vals);
#print Dumper(%normvals);

my $sizemetrics = keys %vals;
write_results("$outd/$outtype.$type.counts.tsv", \\@libs,\\%vals, \\@order, "region") if ($sizemetrics>0);
write_results("$outd/$outtype.$type.tagskb.tsv", \\@libs,\\%normvals, \\@order, "region") if ($sizemetrics>0);

}

sub write_results
{
  my ($outfile, $libs, $vals, $order, $name )=@_;
  open(OUT, ">$outfile");
  print OUT "$name\\t".join("\\t", @{$libs})."\\n";

  my $lib=${$libs}[0];
  foreach my $key ( @order )
  {
    if (exists ${$vals}{$lib}{$key}) {
    print OUT $key;
    foreach my $lib (@{$libs})
    {
      print OUT "\\t".${$vals}{$lib}{$key};
    } 
    print OUT "\\n";
    }
  }
  close(OUT);
}

sub getVals{
  my ($filename, $libname, $vals, $normvals, $lines)=@_;
  if (-e $filename){
     open(IN, $filename);
     while(my $line=<IN>)
     {
       chomp($line);
       my @vals_arr=split(/\\s{2,}/,$line);
       if (exists ${$lines}{$vals_arr[0]}) {
         my $idx=${$lines}{$vals_arr[0]};
         ${$vals}{$libname}{$vals_arr[0]}=$vals_arr[$idx] if (exists $vals_arr[$idx]);
         if ($idx==2) {
             ${$normvals}{$libname}{$vals_arr[0]}=$vals_arr[3] if (exists $vals_arr[3]);
         }
       }
     } 
  }
  
}
'''

}

params.pdfbox_path = "" //* @input
params.picard_path = "" //* @input
//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 32
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 32
    $QUEUE = "short"
}
//* platform
//* autofill
process BAM_Analysis_STAR_Picard {

input:
 set val(name), file(bam) from g126_2_sorted_bam_g180_109

output:
 file "*_metrics"  into g180_109_outputFileOut_g180_82
 file "results/*.pdf"  into g180_109_outputFilePdf_g180_82

when:
(params.run_Picard_CollectMultipleMetrics && (params.run_Picard_CollectMultipleMetrics == "yes")) || !params.run_Picard_CollectMultipleMetrics

script:
"""
java -jar ${params.picard_path} CollectMultipleMetrics OUTPUT=${name}_multiple.out VALIDATION_STRINGENCY=LENIENT INPUT=${bam}
mkdir results && java -jar ${params.pdfbox_path} PDFMerger *.pdf results/${name}_multi_metrics.pdf
"""
}


process BAM_Analysis_STAR_Picard_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.tsv$/) "picard_summary_star/$filename"
	else if (filename =~ /results\/.*.pdf$/) "picard_summary_pdf_star/$filename"
}

input:
 file picardOut from g180_109_outputFileOut_g180_82.collect()
 val mate from g_204_mate_g180_82
 file picardPdf from g180_109_outputFilePdf_g180_82.collect()

output:
 file "*.tsv"  into g180_82_outputFileTSV
 file "results/*.pdf"  into g180_82_outputFilePdf

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

system("mkdir results && mv *.pdf results/. ");

my $indir = $ENV{'PWD'};
my $outd = $ENV{'PWD'};
my @files = ();
my @outtypes = ("CollectRnaSeqMetrics", "alignment_summary_metrics", "base_distribution_by_cycle_metrics", "insert_size_metrics", "quality_by_cycle_metrics", "quality_distribution_metrics" );

foreach my $outtype (@outtypes)
{
my $ext="_multiple.out";
$ext.=".$outtype" if ($outtype ne "CollectRnaSeqMetrics");
@files = <$indir/*$ext>;

my @rowheaders=();
my @libs=();
my %metricvals=();
my %histvals=();

my $pdffile="";
my $libname="";
foreach my $d (@files){
  my $libname=basename($d, $ext);
  print $libname."\\n";
  push(@libs, $libname); 
  getMetricVals($d, $libname, \\%metricvals, \\%histvals, \\@rowheaders);
}

my $sizemetrics = keys %metricvals;
write_results("$outd/$outtype.stats.tsv", \\@libs,\\%metricvals, \\@rowheaders, "metric") if ($sizemetrics>0);
my $sizehist = keys %histvals;
write_results("$outd/$outtype.hist.tsv", \\@libs,\\%histvals, "none", "nt") if ($sizehist>0);

}

sub write_results
{
  my ($outfile, $libs, $vals, $rowheaders, $name )=@_;
  open(OUT, ">$outfile");
  print OUT "$name\\t".join("\\t", @{$libs})."\\n";
  my $size=0;
  $size=scalar(@{${$vals}{${$libs}[0]}}) if(exists ${$libs}[0] and exists ${$vals}{${$libs}[0]} );
  
  for (my $i=0; $i<$size;$i++)
  { 
    my $rowname=$i;
    $rowname = ${$rowheaders}[$i] if ($name=~/metric/);
    print OUT $rowname;
    foreach my $lib (@{$libs})
    {
      print OUT "\\t".${${$vals}{$lib}}[$i];
    } 
    print OUT "\\n";
  }
  close(OUT);
}

sub getMetricVals{
  my ($filename, $libname, $metricvals, $histvals,$rowheaders)=@_;
  if (-e $filename){
     my $nextisheader=0;
     my $nextisvals=0;
     my $nexthist=0;
     open(IN, $filename);
     while(my $line=<IN>)
     {
       chomp($line);
       @{$rowheaders}=split(/\\t/, $line) if ($nextisheader && !scalar(@{$rowheaders})); 
       if ($nextisvals) {
         @{${$metricvals}{$libname}}=split(/\\t/, $line);
         $nextisvals=0;
       }
       if($nexthist){
          my @vals=split(/[\\s\\t]+/,$line); 
          push(@{${$histvals}{$libname}}, $vals[1]) if (exists $vals[1]);
       }
       $nextisvals=1 if ($nextisheader); $nextisheader=0;
       $nextisheader=1 if ($line=~/METRICS CLASS/);
       $nexthist=1 if ($line=~/normalized_position/);
     } 
  }
  
}
'''

}


process STAR_Module_STAR_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.(out|tab)$/) "star/$filename"
}

input:
 set val(name), file(alignSum) from g126_5_outputFileOut_g126_1.groupTuple()
 set val(name), file(LogOut) from g126_5_logOut_g126_1.groupTuple()
 set val(name), file(progressOut) from g126_5_progressOut_g126_1.groupTuple()
 set val(name), file(TabOut) from g126_5_outputFileTab_g126_1.groupTuple()

output:
 file "*.tsv"  into g126_1_outputFile_g126_11
 set "*.{out,tab}"  into g126_1_logOut_g_177
 val "star_alignment_sum"  into g126_1_name_g126_11

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage; 
use Data::Dumper;

my %tsv;
my @headers = ();
my $name = "!{name}";

# merge output files 
`cat !{alignSum} >${name}_Merged_Log.final.out`;
`cat !{LogOut} >${name}_Merged_Log.out`;
`cat !{progressOut} >${name}_Merged_Log.progress.out`;
`cat !{TabOut} >${name}_Merged_SJ.out.tab`;

alteredAligned();

my @keys = keys %tsv;
my $summary = "$name"."_star_sum.tsv";
my $header_string = join("\\t", @headers);
`echo "$header_string" > $summary`;
foreach my $key (@keys){
	my $values = join("\\t", @{ $tsv{$key} });
	`echo "$values" >> $summary`;
}


sub alteredAligned
{
	my @files = qw(!{alignSum});
	my $multimappedSum;
	my $alignedSum;
	my $inputCountSum;
	push(@headers, "Sample");
    push(@headers, "Total Reads");
	push(@headers, "Multimapped Reads Aligned (STAR)");
	push(@headers, "Unique Reads Aligned (STAR)");
	foreach my $file (@files){
		my $multimapped;
		my $aligned;
		my $inputCount;
		chomp($inputCount = `cat $file | grep 'Number of input reads' | awk '{sum+=\\$6} END {print sum}'`);
		chomp($aligned = `cat $file | grep 'Uniquely mapped reads number' | awk '{sum+=\\$6} END {print sum}'`);
		chomp($multimapped = `cat $file | grep 'Number of reads mapped to multiple loci' | awk '{sum+=\\$9} END {print sum}'`);
		$multimappedSum += int($multimapped);
        $alignedSum += int($aligned);
        $inputCountSum += int($inputCount);
	}
	$tsv{$name} = [$name, $inputCountSum];
	push(@{$tsv{$name}}, $multimappedSum);
	push(@{$tsv{$name}}, $alignedSum);
}
'''

}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 10
    $QUEUE = "short"
}
//* platform
//* autofill
process MultiQC {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /multiqc_report.html$/) "multiqc/$filename"
}

input:
 file "tophat/*" from g164_0_summary_g_177.flatten().toList()
 file "rsem/*" from g162_13_rsemOut_g_177.flatten().toList()
 file "star/*" from g126_1_logOut_g_177.flatten().toList()
 file "fastqc/*" from g194_3_FastQCout_g_177.flatten().toList()
 file "sequential_mapping/*" from g195_25_bowfiles_g_177.flatten().toList()
 file "rseqc_tophat/*" from g190_110_outputFileOut_g_177.flatten().toList()
 file "rseqc_rsem/*" from g178_110_outputFileOut_g_177.flatten().toList()
 file "rseqc_star/*" from g180_110_outputFileOut_g_177.flatten().toList()
 file "rseqc_hisat/*" from g184_110_outputFileOut_g_177.flatten().toList()

output:
 file "multiqc_report.html" optional true  into g_177_htmlout

"""
multiqc -e general_stats -d -dd 2 .
"""
}


process STAR_Module_merge_tsv_files_with_same_header {

input:
 file tsv from g126_1_outputFile_g126_11.collect()
 val outputFileName from g126_1_name_g126_11.collect()

output:
 file "${name}.tsv"  into g126_11_outputFileTSV_g_198

script:
name = outputFileName[0]
"""    
awk 'FNR==1 && NR!=1 {  getline; } 1 {print} ' *.tsv > ${name}.tsv
"""
}

mappingListQuoteSep = mapList.collect{ '"' + it + '"'}.join(",") 
rawIndexList = indexList.collect{ '"' + it + '"'}.join(",") 
process Sequential_Mapping_Module_Deduplication_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /deduplication_summary.tsv$/) "sequential_mapping_summary/$filename"
}

input:
 file flagstat from g195_25_log_file_g195_30.collect()
 val mate from g_204_mate_g195_30

output:
 file "deduplication_summary.tsv"  into g195_30_outputFileTSV

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage;
use Data::Dumper;

my @header;
my %all_files;
my %tsv;
my %headerHash;
my %headerText;

my $i=0;
chomp(my $contents = `ls *_duplicates_stats.log`);
my @files = split(/[\\n]+/, $contents);
foreach my $file (@files){
    $i++;
    $file=~/(.*)@(.*)@(.*)_duplicates_stats\\.log/;
    my $mapOrder = int($1); 
    my $mapper = $2; #mapped element 
    my $name = $3; ##sample name
    push(@header, $mapper) unless grep{$_ eq $mapper} @header; 
        
    my $duplicates;
    my $aligned;
    my $dedup;
    my $percent=0;
    chomp($aligned = `cat $file | grep 'mapped (' | awk '{sum+=\\$1+\\$3} END {print sum}'`);
    chomp($duplicates = `cat $file | grep 'duplicates' | awk '{sum+=\\$1+\\$3} END {print sum}'`);
    $dedup = int($aligned) - int($duplicates);
    if ("!{mate}" eq "pair" ){
       $dedup = int($dedup/2);
       $aligned = int($aligned/2);
    } 
    $percent = "0.00";
    if (int($aligned)  > 0 ){
       $percent = sprintf("%.2f", ($aligned-$dedup)/$aligned*100); 
    } 
    $tsv{$name}{$mapper}=[$aligned,$dedup,"$percent%"];
    $headerHash{$mapOrder}=$mapper;
    $headerText{$mapOrder}=["$mapper (Before Dedup)", "$mapper (After Dedup)", "$mapper (Duplication Ratio %)"];
}

my @mapOrderArray = ( keys %headerHash );
my @sortedOrderArray = sort { $a <=> $b } @mapOrderArray;

my $summary = "deduplication_summary.tsv";
open(OUT, ">$summary");
print OUT "Sample\\t";
my @headArr = ();
for my $mapOrder (@sortedOrderArray) {
    push (@headArr, @{$headerText{$mapOrder}});
}
my $headArrAll = join("\\t", @headArr);
print OUT "$headArrAll\\n";

foreach my $name (keys %tsv){
    my @rowArr = ();
    for my $mapOrder (@sortedOrderArray) {
        push (@rowArr, @{$tsv{$name}{$headerHash{$mapOrder}}});
    }
    my $rowArrAll = join("\\t", @rowArr);
    print OUT "$name\\t$rowArrAll\\n";
}
close(OUT);
'''
}

mappingListQuoteSep = mapList.collect{ '"' + it + '"'}.join(",") 
rawIndexList = indexList.collect{ '"' + it + '"'}.join(",") 
process Sequential_Mapping_Module_Sequential_Mapping_Dedup_Bam_count {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.counts.tsv$/) "sequential_mapping_counts/$filename"
}

input:
 file bam from g195_25_bam_file_g195_27.collect()
 file index from g195_25_bam_index_g195_27.collect()

output:
 file "*.counts.tsv"  into g195_27_outputFileTSV

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use File::Basename;
use Getopt::Long;
use Pod::Usage;
use Data::Dumper;

my @header;
my %all_files;

my @mappingList = (!{mappingListQuoteSep});
my @rawIndexList = (!{rawIndexList});
my %indexHash;
my $dedup = "";
@indexHash{@mappingList} = @rawIndexList;

chomp(my $contents = `ls *.bam`);
my @files = split(/[\\n]+/, $contents);
foreach my $file (@files){
        $file=~/(.*)@(.*)_sorted(.*)\\.bam/;
        my $mapper = $1; 
        my $name = $2; ##header
        print $3;
        if ($3 eq ".dedup"){
            $dedup = "dedup.";
        }
        push(@header, $name) unless grep{$_ eq $name} @header; #mapped element header
        push @{$all_files{$mapper}}, $file;
}


open OUT, ">header.tsv";
print OUT join ("\\t", "id","len",@header),"\\n";
close OUT;

foreach my $key (sort keys %all_files) {  
    my @array = @{ $all_files{$key} };  
        unless (-e "$indexHash{$key}.bed") {
        print "2: bed not found run makeBed\\n";
            if (-e "$indexHash{$key}.fa") { 
                makeBed("$indexHash{$key}.fa", $key, "$indexHash{$key}.bed");
            } elsif(-e "$indexHash{$key}.fasta"){
                makeBed("$indexHash{$key}.fasta", $key, "$indexHash{$key}.bed");
            }
        }
    
        my $bamFiles = join ' ', @array;
        print "bedtools multicov -bams $bamFiles -bed $indexHash{$key}.bed >$key.${dedup}counts.tmp\\n";
        `bedtools multicov -bams $bamFiles -bed $indexHash{$key}.bed >$key.${dedup}counts.tmp`;
        my $iniResColumn = int(countColumn("$indexHash{$key}.bed")) + 1;
        `awk -F \\"\\\\t\\" \\'{a=\\"\\";for (i=$iniResColumn;i<=NF;i++){a=a\\"\\\\t\\"\\$i;} print \\$4\\"\\\\t\\"(\\$3-\\$2)\\"\\"a}\\' $key.${dedup}counts.tmp> $key.${dedup}counts.tsv`;
        `sort -k3,3nr $key.${dedup}counts.tsv>$key.${dedup}sorted.tsv`;
        `cat header.tsv $key.${dedup}sorted.tsv> $key.${dedup}counts.tsv`;
}

sub countColumn {
    my ( \$file) = @_;
    open(IN, \$file);
    my $line=<IN>;
    chomp($line);
    my @cols = split('\\t', $line);
    my $n = @cols;
    close OUT;
    return $n;
}

sub makeBed {
    my ( \$fasta, \$type, \$bed) = @_;
    print "makeBed $fasta\\n";
    print "makeBed $bed\\n";
    open OUT, ">$bed";
    open(IN, \$fasta);
    my $name="";
    my $seq="";
    my $i=0;
    while(my $line=<IN>){
        chomp($line);
        if($line=~/^>(.*)/){
            $i++ if (length($seq)>0);
            print OUT "$name\\t1\\t".length($seq)."\\t$name\\t0\\t+\\n" if (length($seq)>0); 
            $name="$1";
            $seq="";
        } elsif($line=~/[ACGTNacgtn]+/){
            $seq.=$line;
        }
    }
    print OUT "$name\\t1\\t".length($seq)."\\t$name\\t0\\t+\\n" if (length($seq)>0); 
    close OUT;
}

'''


}

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 240
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "short"
}
//* platform
//* autofill
process Sequential_Mapping_Module_Sequential_Mapping_Summary {

input:
 set val(name), file(bowfile) from g195_25_bowfiles_g195_26
 val mate from g_204_mate_g195_26
 val filtersList from g195_25_filter_g195_26

output:
 file '*.tsv'  into g195_26_outputFileTSV_g195_13
 val "sequential_mapping_sum"  into g195_26_name_g195_13

shell:
'''
#!/usr/bin/env perl
open(my \$fh, '>', "!{name}.tsv");
print $fh "Sample\\tGroup\\tTotal Reads\\tReads After Filtering\\tUniquely Mapped\\tMultimapped\\tMapped\\n";
my @bowArray = split(' ', "!{bowfile}");
my $group= "\\t";
my @filterArray = (!{filtersList});
foreach my $bowitem(@bowArray) {
    # get mapping id
    my @bowAr = $bowitem.split("_");
    $bowCount = $bowAr[0] + -1;
    # if bowfiles ends with underscore (eg. bow_rRNA), parse rRNA as a group.
    my ($RDS_In, $RDS_After, $RDS_Uniq, $RDS_Multi, $ALGN_T, $a, $b, $aPer, $bPer)=(0, 0, 0, 0, 0, 0, 0, 0, 0);
    if ($bowitem =~ m/bow_([^\\.]+)$/){
        $group = "$1\\t";
        open(IN, $bowitem);
        my $i = 0;
        while(my $line=<IN>){
            chomp($line);
            $line=~s/^ +//;
            my @arr=split(/ /, $line);
            $RDS_In=$arr[0] if ($i=~/^1$/);
            # Reads After Filtering column depends on filtering type
            if ($i == 2){
                if ($filterArray[$bowCount] eq "Yes"){
                    $RDS_After=$arr[0];
                } else {
                    $RDS_After=$RDS_In;
                }
            }
            if ($i == 3){
                $a=$arr[0];
                $aPer=$arr[1];
                $aPer=~ s/([()])//g;
                $RDS_Uniq=$arr[0];
            }
            if ($i == 4){
                $b=$arr[0];
                $bPer=$arr[1];
                $bPer=~ s/([()])//g;
                $RDS_Multi=$arr[0];
            }
            $ALGN_T=($a+$b) if (($i == 5 && "!{mate}" ne "pair" ) || ($i == 13 && "!{mate}" eq "pair" )) ;
            $i++;
        }
        close(IN);
    } elsif ($bowitem =~ m/star_([^\\.]+)$/){
        $group = "$1\\t";
        open(IN2, $bowitem);
        my $multimapped;
		my $aligned;
		my $inputCount;
		chomp($inputCount = `cat $bowitem | grep 'Number of input reads' | awk '{sum+=\\$6} END {print sum}'`);
		chomp($uniqAligned = `cat $bowitem | grep 'Uniquely mapped reads number' | awk '{sum+=\\$6} END {print sum}'`);
		chomp($multimapped = `cat $bowitem | grep 'Number of reads mapped to multiple loci' | awk '{sum+=\\$9} END {print sum}'`);
		## Here we exclude "Number of reads mapped to too many loci" from multimapped reads since in bam file it called as unmapped.
		## Besides, these "too many loci" reads exported as unmapped reads from STAR.
		$RDS_In = int($inputCount);
		$RDS_Multi = int($multimapped);
        $RDS_Uniq = int($uniqAligned);
        $ALGN_T = $RDS_Uniq+$RDS_Multi;
		if ($filterArray[$bowCount] eq "Yes"){
            $RDS_After=$RDS_In-$ALGN_T;
        } else {
            $RDS_After=$RDS_In;
        }
    } elsif ($bowitem =~ m/bow1_([^\\.]+)$/){
        $group = "$1\\t";
        open(IN2, $bowitem);
        my $multimapped;
		my $aligned;
		my $inputCount;
		my $uniqAligned;
		chomp($inputCount = `cat $bowitem | grep '# reads processed:' | awk '{sum+=\\$4} END {print sum}'`);
		chomp($aligned = `cat $bowitem | grep '# reads with at least one reported alignment:' | awk '{sum+=\\$9} END {print sum}'`);
		chomp($uniqAligned = `cat $bowitem | grep '# unique mapped reads:' | awk '{sum+=\\$5} END {print sum}'`);
		## Here we exclude "Number of reads mapped to too many loci" from multimapped reads since in bam file it called as unmapped.
		## Besides, these "too many loci" reads exported as unmapped reads from STAR.
		$RDS_In = int($inputCount);
		$RDS_Multi = int($aligned) -int($uniqAligned);
        $RDS_Uniq = int($uniqAligned);
        $ALGN_T = int($aligned);
		if ($filterArray[$bowCount] eq "Yes"){
            $RDS_After=$RDS_In-$ALGN_T;
        } else {
            $RDS_After=$RDS_In;
        }
    }
    
    print $fh "!{name}\\t$group$RDS_In\\t$RDS_After\\t$RDS_Uniq\\t$RDS_Multi\\t$ALGN_T\\n";
}
close($fh);



'''

}

mappingListQuoteSep = mapList.collect{ '"' + it + '"'}.join(",") 
rawIndexList = indexList.collect{ '"' + it + '"'}.join(",") 
process Sequential_Mapping_Module_Sequential_Mapping_Bam_count {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.counts.tsv$/) "sequential_mapping_counts/$filename"
}

input:
 file bam from g195_25_bam_file_g195_23.collect()
 file index from g195_25_bam_index_g195_23.collect()

output:
 file "*.counts.tsv"  into g195_23_outputFileTSV

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use File::Basename;
use Getopt::Long;
use Pod::Usage;
use Data::Dumper;

my @header;
my %all_files;

my @mappingList = (!{mappingListQuoteSep});
my @rawIndexList = (!{rawIndexList});
my %indexHash;
my $dedup = "";
@indexHash{@mappingList} = @rawIndexList;

chomp(my $contents = `ls *.bam`);
my @files = split(/[\\n]+/, $contents);
foreach my $file (@files){
        $file=~/(.*)@(.*)_sorted(.*)\\.bam/;
        my $mapper = $1; 
        my $name = $2; ##header
        print $3;
        if ($3 eq ".dedup"){
            $dedup = "dedup.";
        }
        push(@header, $name) unless grep{$_ eq $name} @header; #mapped element header
        push @{$all_files{$mapper}}, $file;
}


open OUT, ">header.tsv";
print OUT join ("\\t", "id","len",@header),"\\n";
close OUT;

foreach my $key (sort keys %all_files) {  
    my @array = @{ $all_files{$key} };  
        unless (-e "$indexHash{$key}.bed") {
        print "2: bed not found run makeBed\\n";
            if (-e "$indexHash{$key}.fa") { 
                makeBed("$indexHash{$key}.fa", $key, "$indexHash{$key}.bed");
            } elsif(-e "$indexHash{$key}.fasta"){
                makeBed("$indexHash{$key}.fasta", $key, "$indexHash{$key}.bed");
            }
        }
    
        my $bamFiles = join ' ', @array;
        print "bedtools multicov -bams $bamFiles -bed $indexHash{$key}.bed >$key.${dedup}counts.tmp\\n";
        `bedtools multicov -bams $bamFiles -bed $indexHash{$key}.bed >$key.${dedup}counts.tmp`;
        my $iniResColumn = int(countColumn("$indexHash{$key}.bed")) + 1;
        `awk -F \\"\\\\t\\" \\'{a=\\"\\";for (i=$iniResColumn;i<=NF;i++){a=a\\"\\\\t\\"\\$i;} print \\$4\\"\\\\t\\"(\\$3-\\$2)\\"\\"a}\\' $key.${dedup}counts.tmp> $key.${dedup}counts.tsv`;
        `sort -k3,3nr $key.${dedup}counts.tsv>$key.${dedup}sorted.tsv`;
        `cat header.tsv $key.${dedup}sorted.tsv> $key.${dedup}counts.tsv`;
}

sub countColumn {
    my ( \$file) = @_;
    open(IN, \$file);
    my $line=<IN>;
    chomp($line);
    my @cols = split('\\t', $line);
    my $n = @cols;
    close OUT;
    return $n;
}

sub makeBed {
    my ( \$fasta, \$type, \$bed) = @_;
    print "makeBed $fasta\\n";
    print "makeBed $bed\\n";
    open OUT, ">$bed";
    open(IN, \$fasta);
    my $name="";
    my $seq="";
    my $i=0;
    while(my $line=<IN>){
        chomp($line);
        if($line=~/^>(.*)/){
            $i++ if (length($seq)>0);
            print OUT "$name\\t1\\t".length($seq)."\\t$name\\t0\\t+\\n" if (length($seq)>0); 
            $name="$1";
            $seq="";
        } elsif($line=~/[ACGTNacgtn]+/){
            $seq.=$line;
        }
    }
    print OUT "$name\\t1\\t".length($seq)."\\t$name\\t0\\t+\\n" if (length($seq)>0); 
    close OUT;
}

'''


}


process Adapter_Trimmer_Quality_Module_Quality_Filtering_Summary {

input:
 file logfile from g194_14_log_file_g194_16.collect()
 val mate from g_204_mate_g194_16

output:
 file "quality_filter_summary.tsv"  into g194_16_outputFileTSV_g_198

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage;
use Data::Dumper;

my @header;
my %all_files;
my %tsv;
my %headerHash;
my %headerText;


my $i = 0;
chomp( my $contents = `ls *.fastx_quality.log` );
my @files = split( /[\\n]+/, $contents );
foreach my $file (@files) {
    $i++;
    my $mapper   = "fastx";
    my $mapOrder = "1";
    $file =~ /(.*).fastx_quality\\.log/;
    my $name = $1;    ##sample name
    push( @header, "fastx" );

    my $in;
    my $out;


    chomp( $in =`cat $file | grep 'Input:' | awk '{sum+=\\$2} END {print sum}'` );
    chomp( $out =`cat $file | grep 'Output:' | awk '{sum+=\\$2} END {print sum}'` );
   

    $tsv{$name}{$mapper} = [ $in, $out ];
    $headerHash{$mapOrder} = $mapper;
    $headerText{$mapOrder} = [ "Total Reads", "Reads After Quality Filtering" ];
    
}

my @mapOrderArray = ( keys %headerHash );
my @sortedOrderArray = sort { $a <=> $b } @mapOrderArray;

my $summary          = "quality_filter_summary.tsv";
writeFile( $summary,          \\%headerText,       \\%tsv );

sub writeFile {
    my $summary    = $_[0];
    my %headerText = %{ $_[1] };
    my %tsv        = %{ $_[2] };
    open( OUT, ">$summary" );
    print OUT "Sample\\t";
    my @headArr = ();
    for my $mapOrder (@sortedOrderArray) {
        push( @headArr, @{ $headerText{$mapOrder} } );
    }
    my $headArrAll = join( "\\t", @headArr );
    print OUT "$headArrAll\\n";

    foreach my $name ( keys %tsv ) {
        my @rowArr = ();
        for my $mapOrder (@sortedOrderArray) {
            push( @rowArr, @{ $tsv{$name}{ $headerHash{$mapOrder} } } );
        }
        my $rowArrAll = join( "\\t", @rowArr );
        print OUT "$name\\t$rowArrAll\\n";
    }
    close(OUT);
}

'''
}


process Sequential_Mapping_Module_Merge_TSV_Files {

input:
 file tsv from g195_26_outputFileTSV_g195_13.collect()
 val outputFileName from g195_26_name_g195_13.collect()

output:
 file "${name}.tsv"  into g195_13_outputFileTSV_g195_14

script:
name = outputFileName[0]
"""    
awk 'FNR==1 && NR!=1 {  getline; } 1 {print} ' *.tsv > ${name}.tsv
"""
}


process Sequential_Mapping_Module_Sequential_Mapping_Short_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /sequential_mapping_short_sum.tsv$/) "sequential_mapping_summary/$filename"
	else if (filename =~ /sequential_mapping_detailed_sum.tsv$/) "sequential_mapping_summary/$filename"
}

input:
 file mainSum from g195_13_outputFileTSV_g195_14

output:
 file "sequential_mapping_short_sum.tsv"  into g195_14_outputFileTSV_g_198
 file "sequential_mapping_detailed_sum.tsv"  into g195_14_outputFile

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use File::Basename;
use Getopt::Long;
use Pod::Usage;
use Data::Dumper;

my @header;
my %all_rows;
my @seen_cols_short;
my @seen_cols_detailed;
my $ID_header;

chomp(my $contents = `ls *.tsv`);
my @files = split(/[\\n]+/, $contents);
foreach my $file (@files){
        open IN,"$file";
        my $line1 = <IN>;
        chomp($line1);
        ( $ID_header, my @h) = ( split("\\t", $line1) );
        my $totalHeader = $h[1];
        my $afterFilteringHeader = $h[2];
        my $uniqueHeader = $h[3];
        my $multiHeader = $h[4];
        my $mappedHeader = $h[5];
        push(@seen_cols_short, $totalHeader) unless grep{$_ eq $totalHeader} @seen_cols_short; #Total reads Header
        push(@seen_cols_detailed, $totalHeader) unless grep{$_ eq $totalHeader} @seen_cols_detailed; #Total reads Header

        my $n=0;
        while (my $line=<IN>) {
                
                chomp($line);
                my ( $ID, @fields ) = ( split("\\t", $line) ); 
                #SHORT
                push(@seen_cols_short, $fields[0]) unless grep{$_ eq $fields[0]} @seen_cols_short; #mapped element header
                $all_rows{$ID}{$fields[0]} = $fields[5];#Mapped Reads
                #Grep first line $fields[1] as total reads.
                if (!exists $all_rows{$ID}{$totalHeader}){    
                        $all_rows{$ID}{$totalHeader} = $fields[1];
                } 
                $all_rows{$ID}{$afterFilteringHeader} = $fields[2]; #only use last entry
                #DETAILED
                $uniqueHeadEach = "$fields[0] (${uniqueHeader})";
                $multiHeadEach = "$fields[0] (${multiHeader})";
                $mappedHeadEach = "$fields[0] (${mappedHeader})";
                push(@seen_cols_detailed, $mappedHeadEach) unless grep{$_ eq $mappedHeadEach} @seen_cols_detailed;
                push(@seen_cols_detailed, $uniqueHeadEach) unless grep{$_ eq $uniqueHeadEach} @seen_cols_detailed;
                push(@seen_cols_detailed, $multiHeadEach) unless grep{$_ eq $multiHeadEach} @seen_cols_detailed;
                $all_rows{$ID}{$mappedHeadEach} = $fields[5];
                $all_rows{$ID}{$uniqueHeadEach} = $fields[3];
                $all_rows{$ID}{$multiHeadEach} = $fields[4];
    }
    close IN;
    push(@seen_cols_short, $afterFilteringHeader) unless grep{$_ eq $afterFilteringHeader} @seen_cols_short; #After filtering Header
}


#print Dumper \\%all_rows;
#print Dumper \\%seen_cols_short;

printFiles("sequential_mapping_short_sum.tsv",@seen_cols_short,);
printFiles("sequential_mapping_detailed_sum.tsv",@seen_cols_detailed);


sub printFiles {
    my($summary, @cols_to_print) = @_;
    
    open OUT, ">$summary";
    print OUT join ("\\t", $ID_header,@cols_to_print),"\\n";
    foreach my $key ( keys %all_rows ) { 
        print OUT join ("\\t", $key, (map { $all_rows{$key}{$_} // '' } @cols_to_print)),"\\n";
        }
        close OUT;
}

'''


}

g126_11_outputFileTSV_g_198= g126_11_outputFileTSV_g_198.ifEmpty(file('starSum', type: 'any')) 
g195_14_outputFileTSV_g_198= g195_14_outputFileTSV_g_198.ifEmpty(file('sequentialSum', type: 'any')) 
g163_10_outputFileTSV_g_198= g163_10_outputFileTSV_g_198.ifEmpty(file('hisatSum', type: 'any')) 
g162_17_outputFileTSV_g_198= g162_17_outputFileTSV_g_198.ifEmpty(file('rsemSum', type: 'any')) 
g164_9_outputFileTSV_g_198= g164_9_outputFileTSV_g_198.ifEmpty(file('tophatSum', type: 'any')) 
g194_11_outputFileTSV_g_198= g194_11_outputFileTSV_g_198.ifEmpty(file('adapterSum', type: 'any')) 
g194_16_outputFileTSV_g_198= g194_16_outputFileTSV_g_198.ifEmpty(file('qualitySum', type: 'any')) 

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 30
    $CPU  = 1
    $MEMORY = 10
    $QUEUE = "short"
}
//* platform
//* autofill
process Overall_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /overall_summary.tsv$/) "summary/$filename"
}

input:
 file starSum from g126_11_outputFileTSV_g_198
 file sequentialSum from g195_14_outputFileTSV_g_198
 file hisatSum from g163_10_outputFileTSV_g_198
 file rsemSum from g162_17_outputFileTSV_g_198
 file tophatSum from g164_9_outputFileTSV_g_198
 file adapterSum from g194_11_outputFileTSV_g_198
 file qualitySum from g194_16_outputFileTSV_g_198

output:
 file "overall_summary.tsv"  into g_198_outputFileTSV

shell:
'''
#!/usr/bin/env perl
use List::Util qw[min max];
use strict;
use File::Basename;
use Getopt::Long;
use Pod::Usage;
use Data::Dumper;

my @header;
my %all_rows;
my @seen_cols;
my $ID_header;

chomp(my $contents = `ls *.tsv`);
my @rawFiles = split(/[\\n]+/, $contents);
my @files = ();
my @order = ("adapter_removal","trimmer","quality","extractUMI","sequential_mapping", "star", "rsem", "hisat2", "tophat2");
for ( my $k = 0 ; $k <= $#order ; $k++ ) {
    for ( my $i = 0 ; $i <= $#rawFiles ; $i++ ) {
        if ( $rawFiles[$i] =~ /$order[$k]/ ) {
            push @files, $rawFiles[$i];
        }
    }
}

print Dumper \\@files;
##add rest of the files
for ( my $i = 0 ; $i <= $#rawFiles ; $i++ ) {
    push(@files, $rawFiles[$i]) unless grep{$_ == $rawFiles[$i]} @files;
}
print Dumper \\@files;

##Merge each file according to array order

foreach my $file (@files){
        open IN,"$file";
        my $line1 = <IN>;
        chomp($line1);
        ( $ID_header, my @header) = ( split("\\t", $line1) );
        push @seen_cols, @header;

        while (my $line=<IN>) {
        chomp($line);
        my ( $ID, @fields ) = ( split("\\t", $line) ); 
        my %this_row;
        @this_row{@header} = @fields;

        #print Dumper \\%this_row;

        foreach my $column (@header) {
            if (! exists $all_rows{$ID}{$column}) {
                $all_rows{$ID}{$column} = $this_row{$column}; 
            }
        }   
    }
    close IN;
}

#print for debugging
#print Dumper \\%all_rows;
#print Dumper \\%seen_cols;

#grab list of column headings we've seen, and order them. 
my @cols_to_print = uniq(@seen_cols);
my $summary = "overall_summary.tsv";
open OUT, ">$summary";
print OUT join ("\\t", $ID_header,@cols_to_print),"\\n";
foreach my $key ( keys %all_rows ) { 
    #map iterates all the columns, and gives the value or an empty string. if it's undefined. (prevents errors)
    print OUT join ("\\t", $key, (map { $all_rows{$key}{$_} // '' } @cols_to_print)),"\\n";
}
close OUT;

sub uniq {
    my %seen;
    grep ! $seen{$_}++, @_;
}

'''


}


workflow.onComplete {
println "##Pipeline execution summary##"
println "---------------------------"
println "##Completed at: $workflow.complete"
println "##Duration: ${workflow.duration}"
println "##Success: ${workflow.success ? 'OK' : 'failed' }"
println "##Exit status: ${workflow.exitStatus}"
}
