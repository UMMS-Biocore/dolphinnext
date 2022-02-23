params.outdir = 'results'  

params.genome_build = "" //* @dropdown @options:"human_hg19, human_hg38_genecode, mouse_mm10, mousetest_mm10, drosophila_melanogaster_dm3, custom"
params.version_of_10x = "" //* @dropdown @options:"V1-2014, V2-2016, V3-2018, custom"
params.run_STAR = "yes" //* @dropdown @options:"yes","no" @show_settings:"Map_STAR"
params.run_Tophat = "no" //* @dropdown @options:"yes","no" @show_settings:"Map_Tophat2"
params.run_HISAT2 = "no" //* @dropdown @options:"yes","no" @show_settings:"Map_HISAT2"
params.run_Single_Cell_Module = "yes" //* @dropdown @options:"yes","no" @show_settings:"filter_lowCount","ESAT"
def _species;
def _build;
def _share;
//* autofill
if (params.version_of_10x == "V1-2014"){
    _cellBarcodeFile = "737K-april-2014_rc-V1.txt"
    params.cellBarcodePattern = "(?P<cell_1>.{16})(?P<umi_1>.{10})"
} else if (params.version_of_10x == "V2-2016"){
    _cellBarcodeFile = "737K-august-2016-V2.txt"
    params.cellBarcodePattern = "(?P<cell_1>.{16})(?P<umi_1>.{10})"
} else if (params.version_of_10x == "V3-2018"){
    _cellBarcodeFile = "3M-february-2018-V3.txt"
    params.cellBarcodePattern = "(?P<cell_1>.{16})(?P<umi_1>.{12})"
} 
if (params.genome_build == "mousetest_mm10"){
    _species = "mousetest"
    _build = "mm10"
    _trans2gene = "mm10_trans2gene_NMaug.txt"
} else if (params.genome_build == "human_hg38_genecode"){
    _species = "human"
    _build = "hg38_genecode"
    _trans2gene = "hg38_gencode_v28_basic_trans2gene.txt"
} else if (params.genome_build == "human_hg19"){
    _species = "human"
    _build = "hg19"
    _trans2gene = "hg19_trans2gene_NMaug.txt"
} else if (params.genome_build == "mouse_mm10"){
    _species = "mouse"
    _build = "mm10"
    _trans2gene = "mm10_trans2gene_NMaug.txt"
} else if (params.genome_build == "drosophila_melanogaster_dm3"){
    _species = "d_melanogaster"
    _build = "dm3"
    _trans2gene = "dm3_trans2gene_refseq.txt"
}
if ($HOSTNAME == "default"){
    _shareGen = "/mnt/efs/share/genome_data"
    _shareSC = "/mnt/efs/share/singleCell"
    $SINGULARITY_IMAGE = "shub://UMMS-Biocore/singularitysc"
    $SINGULARITY_OPTIONS = "--bind /mnt"
}
//* platform
if ($HOSTNAME == "garberwiki.umassmed.edu"){
    _shareGen = "/share/dolphin/genome_data"
    _shareSC = "/share/garberlab/yukseleo/singleCellPipeline"
} else if ($HOSTNAME == "ghpcc06.umassrc.org"){
    _shareGen = "/share/data/umw_biocore/genome_data"
    _shareSC = "/project/umw_biocore/bin/singleCell"
    $SINGULARITY_OPTIONS = "--bind /project --bind /nl --bind /share"
    $SINGULARITY_IMAGE = "/project/umw_biocore/singularity/UMMS-Biocore-singularitysc-master-latest.simg"
    $TIME = 1000
    $CPU  = 1
    $MEMORY = 32
    $QUEUE = "long"
}else if ($HOSTNAME == "fs-4d79c2ad"){
    _shareGen = "/mnt/efs/share/genome_data"
    _shareSC = "/mnt/efs/share/singleCell"
    $CPU  = 1
    $MEMORY = 10
    $SINGULARITY_IMAGE = "shub://UMMS-Biocore/singularitysc"
    $SINGULARITY_OPTIONS = "--bind /mnt"
}
//* platform
if (params.genome_build && $HOSTNAME){
    params.genome ="${_shareGen}/${_species}/${_build}/${_build}.fa"
    params.genomeDir ="${_shareGen}/${_species}/${_build}/"
    params.gtfFilePath ="${_shareGen}/${_species}/${_build}/ucsc.gtf"
    params.genomeIndexPath ="${_shareGen}/${_species}/${_build}/${_build}"
    params.gene_to_transcript_mapping_file = "${_shareSC}/singleCellFiles/${_trans2gene}"
    
}
if (params.version_of_10x && $HOSTNAME){
    params.cellBarcodeFile = "${_shareSC}/singleCellFiles/${_cellBarcodeFile}"
}
if ($HOSTNAME){
    params.cleanLowEndUmis_path ="${_shareSC}/singleCellScripts/cleanLowEndUmis.py"
	params.countUniqueAlignedBarcodes_fromFile_filePath ="${_shareSC}/singleCellScripts/countUniqueAlignedBarcodes_fromFile.py"
	params.ESAT_path ="${_shareSC}/singleCellScripts/esat.v0.1_09.09.16_24.18.umihack.jar"
	params.filter_lowCountBC_bam_print_py_filePath ="${_shareSC}/singleCellScripts/filter_lowCountBC_bam_split_print.py"
	params.samtools_path = "/usr/local/bin/dolphin-bin/samtools-1.2/samtools"
	params.star_path = "/usr/local/bin/dolphin-bin/STAR"
	params.hisat2_path = "/usr/local/bin/dolphin-bin/hisat2/hisat2"
	params.tophat2_path = "/usr/local/bin/dolphin-bin/tophat2_2.0.12/tophat2"
	params.mate_split = "single"
}
//*
if (!params.cellBarcodeFile){params.cellBarcodeFile = ""} 
if (!params.mate_split){params.mate_split = ""} 
if (!params.reads){params.reads = ""} 
if (!params.mate){params.mate = ""} 
if (!params.cellBarcodePattern){params.cellBarcodePattern = ""} 

Channel.value(params.cellBarcodeFile).set{g_7_cellBarcodeFile_g_72}
Channel.value(params.mate_split).into{g_13_mate_g75_3;g_13_mate_g74_0;g_13_mate_g74_3;g_13_mate_g76_5}
Channel
	.fromFilePairs( params.reads , size: (params.mate != "pair") ? 1 : 2 )
	.ifEmpty { error "Cannot find any read_pairs matching: ${params.reads}" }
	.set{g_27_read_pairs_g_72}

Channel.value(params.mate).into{g_38_mate_g_72;g_38_mate_g_106}
Channel.value(params.cellBarcodePattern).set{g_73_cellBarcodePattern_g_72}

UMIqualityFilterThreshold = "3" //* @input @description: "Discards reads where the UMI contains a base with a Phred score below this threshold. Default value 3, filters out the reads if UMI contains a base assigned as N."
insertSide = "R2" //* @dropdown @options:"R1","R2" 


if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 1000
    $CPU  = 1
    $MEMORY = 10
    $QUEUE = "long"
}
process UmiExtract_10xGenomics {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_valid.fastq$/) "valid_fastq/$filename"
}

input:
 val cellBarcode from g_7_cellBarcodeFile_g_72
 val mate from g_38_mate_g_72
 set val(name), file(reads) from g_27_read_pairs_g_72
 val cellBarcodePattern from g_73_cellBarcodePattern_g_72

output:
 set val(name), file("*_valid.fastq")  into g_72_valid_fastq_g_106

script:
readArray = reads.toString().split(' ')
"""
if [ "${mate}" == "pair" ]; then
umi_tools extract --bc-pattern='${cellBarcodePattern}' \
                  --extract-method=regex \
                  --stdin ${readArray[0]} \
                  --stdout out${name}_R1.fastq \
                  --read2-in ${readArray[1]} \
                  --read2-out=out${name}_R2.fastq \
                  --filter-cell-barcode \
                  --whitelist=${cellBarcode} \
				  --quality-filter-threshold=${UMIqualityFilterThreshold} \
				  --quality-encoding=phred33
mv out${name}_${insertSide}.fastq ${name}_valid.fastq
else
umi_tools extract --bc-pattern='${cellBarcodePattern}' \
                  --extract-method=regex \
                  --stdin ${reads} \
                  --stdout ${name}_valid.fastq \
                  --filter-cell-barcode \
                  --whitelist=${cellBarcode} \
				  --quality-filter-threshold= ${UMIqualityFilterThreshold} \
				  --quality-encoding=phred33
fi
sed -i 's/_/:/g' *valid.fastq
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
g_72_valid_fastq_g_106.into{g_106_reads_g75_3; g_106_reads_g74_0; g_106_reads_g76_5}
} else {

process SplitFastq {

input:
 val mate from g_38_mate_g_106
 set val(name), file(reads) from g_72_valid_fastq_g_106.map(flatPairsClosure).splitFastq(splitFastqParams).map(groupPairsClosure)

output:
 set val(name), file("split/*")  into g_106_reads_g75_3, g_106_reads_g74_0, g_106_reads_g76_5

when:
params.run_Split_Fastq == "yes"

script:
"""    
mkdir -p split
mv ${reads} split/.
"""
}
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
 val mate from g_13_mate_g76_5
 set val(name), file(reads) from g_106_reads_g76_5

output:
 set val(name), file("${newName}Log.final.out")  into g76_5_outputFileOut_g76_1
 set val(name), file("${newName}.flagstat.txt")  into g76_5_outputFileTxt
 set val(name), file("${newName}Log.out")  into g76_5_logOut_g76_1
 set val(name), file("${newName}.bam")  into g76_5_mapped_reads_g76_2
 set val(name), file("${newName}SJ.out.tab")  into g76_5_outputFileTab_g76_1
 set val(name), file("${newName}Log.progress.out")  into g76_5_progressOut_g76_1

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
	if (filename =~ /.*_sorted.*bai$/) "sorted_bam_star/$filename"
	else if (filename =~ /.*_sorted.*bam$/) "sorted_bam_star/$filename"
}

input:
 set val(oldname), file(bamfiles) from g76_5_mapped_reads_g76_2.groupTuple()

output:
 set val(oldname), file("${oldname}.bam")  into g76_2_merged_bams
 set val(oldname), file("*_sorted*bai")  into g76_2_bam_index
 set val(oldname), file("*_sorted*bam")  into g76_2_sorted_bam_g109_85

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

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "long"
}
//* platform
//* autofill
process Single_Cell_Module_after_STAR_samtools_sort_index {

input:
 set val(name), file(bam) from g76_2_sorted_bam_g109_85

output:
 set val(name), file("bam/*.bam")  into g109_85_bam_file_g109_78
 set val(name), file("bam/*.bai")  into g109_85_bam_index_g109_78

script:
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = "samtools index " + bam 
} else {
    runSamtools = "samtools sort " + bam + " " + name +"_sorted && samtools index " + name + "_sorted.bam "
}
"""
$runSamtools
mkdir -p bam && mv *_sorted.ba* bam/.
"""
}

params.countUniqueAlignedBarcodes_fromFile_filePath = "" //* @input

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 1
    $QUEUE = "long"
}
//*
if (!((params.run_Single_Cell_Module && (params.run_Single_Cell_Module == "yes")) || !params.run_Single_Cell_Module)){
g109_85_bam_file_g109_78.into{g109_78_sorted_bam_g109_86}
g109_85_bam_index_g109_78.into{g109_78_bam_index_g109_86}
g109_78_count_file_g109_86 = Channel.empty()
} else {

process Single_Cell_Module_after_STAR_Count_cells {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_count.txt$/) "cell_counts_after_star/$filename"
}

input:
 set val(oldname), file(sorted_bams) from g109_85_bam_file_g109_78
 set val(oldname), file(bams_index) from g109_85_bam_index_g109_78

output:
 set val(oldname), file("bam/*.bam")  into g109_78_sorted_bam_g109_86
 set val(oldname), file("bam/*.bam.bai")  into g109_78_bam_index_g109_86
 set val(oldname), file("*_count.txt")  into g109_78_count_file_g109_86

when:
(params.run_Single_Cell_Module && (params.run_Single_Cell_Module == "yes")) || !params.run_Single_Cell_Module

script:
"""
find  -name "*.bam" > filelist.txt
python ${params.countUniqueAlignedBarcodes_fromFile_filePath} -i filelist.txt -o ${oldname}_count.txt
mkdir bam
mv $sorted_bams bam/.
mv $bams_index bam/.
"""
}
}


params.filter_lowCountBC_bam_print_py_filePath = "" //* @input
cutoff_for_filter = 3000 //* @input @description:"script removes cells from bam files that are below cutoff value (eg. 3000 reads per cell)."
maxCellsForTmpFile = 500 //* @input @description:"maximum number of unique barcodes in each separated tmp file. Used for increasing performance of ESAT"

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 1
    $QUEUE = "long"
}
//*
process Single_Cell_Module_after_STAR_filter_lowCount {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /${name}_filtered_.*.bam$/) "filtered_bam_after_star/$filename"
}

input:
 set val(oldname), file(sorted_bams) from g109_78_sorted_bam_g109_86
 set val(name), file(count_file) from g109_78_count_file_g109_86
 set val(oldname), file(bam_index) from g109_78_bam_index_g109_86

output:
 set val(name), file("${name}_filtered_*.bam")  into g109_86_filtered_bam_g109_87

"""
python ${params.filter_lowCountBC_bam_print_py_filePath} -i ${sorted_bams} -b ${name}_count.txt -o ${name}_filtered.bam -n ${cutoff_for_filter} -c ${maxCellsForTmpFile}
"""
}

esat_parameters = "-task score3p -wLen 100 -wOlap 50 -wExt 1000 -sigTest .01 -multimap ignore -scPrep" //* @input
params.ESAT_path = "" //* @input
params.gene_to_transcript_mapping_file = "" //* @input


//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 40
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 40
    $QUEUE = "long"
}
//* platform
//* autofill
process Single_Cell_Module_after_STAR_ESAT {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.(txt|log)$/) "esat_after_star/$filename"
	else if (filename =~ /.*umi.distributions.txt$/) "esat_after_star/$filename"
}

input:
 set val(name), file(filtered_bam) from g109_86_filtered_bam_g109_87.transpose()

output:
 file "*.{txt,log}"  into g109_87_outputFileTxt
 set val(name), file("*umi.distributions.txt")  into g109_87_UMI_distributions_g109_88

script:
nameAll = filtered_bam.toString()
namePrefix = nameAll - ".bam"
"""    
find  -name "*.bam" | awk '{print "${namePrefix}\t"\$1 }' > ${namePrefix}_filelist.txt
java -Xmx40g -jar ${params.ESAT_path} -alignments ${namePrefix}_filelist.txt -out ${namePrefix}_esat.txt -geneMapping ${params.gene_to_transcript_mapping_file} ${esat_parameters}
mv scripture2.log ${namePrefix}_scripture2.log
"""
}

params.cleanLowEndUmis_path = "" //* @input

//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 20
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 20
    $QUEUE = "long"
}
//* platform
//* autofill
process Single_Cell_Module_after_STAR_UMI_Trim {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_umiClean.txt$/) "UMI_count_final_after_star/$filename"
}

input:
 set val(name), file(umi_dist) from g109_87_UMI_distributions_g109_88.groupTuple()

output:
 set val(name), file("*_umiClean.txt")  into g109_88_UMI_clean

"""
cat ${umi_dist} > ${name}_merged_umi.distributions.txt
python ${params.cleanLowEndUmis_path} \
-i ${name}_merged_umi.distributions.txt \
-o ${name}_umiClean.txt \
-n 2
"""
}


process STAR_Module_STAR_Summary {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.(out|tab)$/) "star/$filename"
}

input:
 set val(name), file(alignSum) from g76_5_outputFileOut_g76_1.groupTuple()
 set val(name), file(LogOut) from g76_5_logOut_g76_1.groupTuple()
 set val(name), file(progressOut) from g76_5_progressOut_g76_1.groupTuple()
 set val(name), file(TabOut) from g76_5_outputFileTab_g76_1.groupTuple()

output:
 file "*.tsv"  into g76_1_outputFile_g76_11
 set "*.{out,tab}"  into g76_1_logOut
 val "star_alignment_sum"  into g76_1_name_g76_11

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


process STAR_Module_merge_tsv_files_with_same_header {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /${name}.tsv$/) "star_alignment_summary/$filename"
}

input:
 file tsv from g76_1_outputFile_g76_11.collect()
 val outputFileName from g76_1_name_g76_11.collect()

output:
 file "${name}.tsv"  into g76_11_outputFileTSV

script:
name = outputFileName[0]
"""    
awk 'FNR==1 && NR!=1 {  getline; } 1 {print} ' *.tsv > ${name}.tsv
"""
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
 val mate from g_13_mate_g74_0
 set val(name), file(reads) from g_106_reads_g74_0

output:
 set val(name), file("${newName}.bam")  into g74_0_mapped_reads_g74_4
 set val(name), file("${newName}_unmapped.bam")  into g74_0_unmapped_reads
 set val(name), file("${newName}_align_summary.txt")  into g74_0_summary_g74_3

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
 set val(name), file(alignSum) from g74_0_summary_g74_3.groupTuple()
 val mate from g_13_mate_g74_3

output:
 set val(name), file("${name}_tophat_sum.tsv")  into g74_3_report_g74_9
 val "tophat2_alignment_sum"  into g74_3_name_g74_9

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

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /${name}.tsv$/) "tophat_alignment_summary/$filename"
}

input:
 file tsv from g74_3_report_g74_9.collect()
 val outputFileName from g74_3_name_g74_9.collect()

output:
 file "${name}.tsv"  into g74_9_outputFileTSV

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
	if (filename =~ /.*_sorted.*bai$/) "sorted_bam_tophat2/$filename"
	else if (filename =~ /.*_sorted.*bam$/) "sorted_bam_tophat2/$filename"
}

input:
 set val(oldname), file(bamfiles) from g74_0_mapped_reads_g74_4.groupTuple()

output:
 set val(oldname), file("${oldname}.bam")  into g74_4_merged_bams
 set val(oldname), file("*_sorted*bai")  into g74_4_bam_index
 set val(oldname), file("*_sorted*bam")  into g74_4_sorted_bam_g107_85

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

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "long"
}
//* platform
//* autofill
process Single_Cell_Module_after_Tophat2_samtools_sort_index {

input:
 set val(name), file(bam) from g74_4_sorted_bam_g107_85

output:
 set val(name), file("bam/*.bam")  into g107_85_bam_file_g107_78
 set val(name), file("bam/*.bai")  into g107_85_bam_index_g107_78

script:
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = "samtools index " + bam 
} else {
    runSamtools = "samtools sort " + bam + " " + name +"_sorted && samtools index " + name + "_sorted.bam "
}
"""
$runSamtools
mkdir -p bam && mv *_sorted.ba* bam/.
"""
}

params.countUniqueAlignedBarcodes_fromFile_filePath = "" //* @input

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 1
    $QUEUE = "long"
}
//*
if (!((params.run_Single_Cell_Module && (params.run_Single_Cell_Module == "yes")) || !params.run_Single_Cell_Module)){
g107_85_bam_file_g107_78.into{g107_78_sorted_bam_g107_86}
g107_85_bam_index_g107_78.into{g107_78_bam_index_g107_86}
g107_78_count_file_g107_86 = Channel.empty()
} else {

process Single_Cell_Module_after_Tophat2_Count_cells {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_count.txt$/) "cell_counts_after_tophat2/$filename"
}

input:
 set val(oldname), file(sorted_bams) from g107_85_bam_file_g107_78
 set val(oldname), file(bams_index) from g107_85_bam_index_g107_78

output:
 set val(oldname), file("bam/*.bam")  into g107_78_sorted_bam_g107_86
 set val(oldname), file("bam/*.bam.bai")  into g107_78_bam_index_g107_86
 set val(oldname), file("*_count.txt")  into g107_78_count_file_g107_86

when:
(params.run_Single_Cell_Module && (params.run_Single_Cell_Module == "yes")) || !params.run_Single_Cell_Module

script:
"""
find  -name "*.bam" > filelist.txt
python ${params.countUniqueAlignedBarcodes_fromFile_filePath} -i filelist.txt -o ${oldname}_count.txt
mkdir bam
mv $sorted_bams bam/.
mv $bams_index bam/.
"""
}
}


params.filter_lowCountBC_bam_print_py_filePath = "" //* @input
cutoff_for_filter = 3000 //* @input @description:"script removes cells from bam files that are below cutoff value (eg. 3000 reads per cell)."
maxCellsForTmpFile = 500 //* @input @description:"maximum number of unique barcodes in each separated tmp file. Used for increasing performance of ESAT"

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 1
    $QUEUE = "long"
}
//*
process Single_Cell_Module_after_Tophat2_filter_lowCount {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /${name}_filtered_.*.bam$/) "filtered_bam_after_tophat2/$filename"
}

input:
 set val(oldname), file(sorted_bams) from g107_78_sorted_bam_g107_86
 set val(name), file(count_file) from g107_78_count_file_g107_86
 set val(oldname), file(bam_index) from g107_78_bam_index_g107_86

output:
 set val(name), file("${name}_filtered_*.bam")  into g107_86_filtered_bam_g107_87

"""
python ${params.filter_lowCountBC_bam_print_py_filePath} -i ${sorted_bams} -b ${name}_count.txt -o ${name}_filtered.bam -n ${cutoff_for_filter} -c ${maxCellsForTmpFile}
"""
}

esat_parameters = "-task score3p -wLen 100 -wOlap 50 -wExt 1000 -sigTest .01 -multimap ignore -scPrep" //* @input
params.ESAT_path = "" //* @input
params.gene_to_transcript_mapping_file = "" //* @input


//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 40
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 40
    $QUEUE = "long"
}
//* platform
//* autofill
process Single_Cell_Module_after_Tophat2_ESAT {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.(txt|log)$/) "esat_after_tophat2/$filename"
	else if (filename =~ /.*umi.distributions.txt$/) "esat_after_tophat2/$filename"
}

input:
 set val(name), file(filtered_bam) from g107_86_filtered_bam_g107_87.transpose()

output:
 file "*.{txt,log}"  into g107_87_outputFileTxt
 set val(name), file("*umi.distributions.txt")  into g107_87_UMI_distributions_g107_88

script:
nameAll = filtered_bam.toString()
namePrefix = nameAll - ".bam"
"""    
find  -name "*.bam" | awk '{print "${namePrefix}\t"\$1 }' > ${namePrefix}_filelist.txt
java -Xmx40g -jar ${params.ESAT_path} -alignments ${namePrefix}_filelist.txt -out ${namePrefix}_esat.txt -geneMapping ${params.gene_to_transcript_mapping_file} ${esat_parameters}
mv scripture2.log ${namePrefix}_scripture2.log
"""
}

params.cleanLowEndUmis_path = "" //* @input

//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 20
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 20
    $QUEUE = "long"
}
//* platform
//* autofill
process Single_Cell_Module_after_Tophat2_UMI_Trim {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_umiClean.txt$/) "UMI_count_final_after_tophat2/$filename"
}

input:
 set val(name), file(umi_dist) from g107_87_UMI_distributions_g107_88.groupTuple()

output:
 set val(name), file("*_umiClean.txt")  into g107_88_UMI_clean

"""
cat ${umi_dist} > ${name}_merged_umi.distributions.txt
python ${params.cleanLowEndUmis_path} \
-i ${name}_merged_umi.distributions.txt \
-o ${name}_umiClean.txt \
-n 2
"""
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
 val mate from g_13_mate_g75_3
 set val(name), file(reads) from g_106_reads_g75_3

output:
 set val(name), file("${newName}.bam")  into g75_3_mapped_reads_g75_1
 set val(name), file("${newName}.align_summary.txt")  into g75_3_outputFileTxt_g75_2
 set val(name), file("${newName}.flagstat.txt")  into g75_3_outputFileOut

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
	if (filename =~ /.*_sorted.*bai$/) "sorted_bam_hisat2/$filename"
	else if (filename =~ /.*_sorted.*bam$/) "sorted_bam_hisat2/$filename"
}

input:
 set val(oldname), file(bamfiles) from g75_3_mapped_reads_g75_1.groupTuple()

output:
 set val(oldname), file("${oldname}.bam")  into g75_1_merged_bams
 set val(oldname), file("*_sorted*bai")  into g75_1_bam_index
 set val(oldname), file("*_sorted*bam")  into g75_1_sorted_bam_g108_85

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

//* autofill
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 8
    $QUEUE = "long"
}
//* platform
//* autofill
process Single_Cell_Module_after_HISAT2_samtools_sort_index {

input:
 set val(name), file(bam) from g75_1_sorted_bam_g108_85

output:
 set val(name), file("bam/*.bam")  into g108_85_bam_file_g108_78
 set val(name), file("bam/*.bai")  into g108_85_bam_index_g108_78

script:
nameAll = bam.toString()
if (nameAll.contains('_sorted.bam')) {
    runSamtools = "samtools index " + bam 
} else {
    runSamtools = "samtools sort " + bam + " " + name +"_sorted && samtools index " + name + "_sorted.bam "
}
"""
$runSamtools
mkdir -p bam && mv *_sorted.ba* bam/.
"""
}

params.countUniqueAlignedBarcodes_fromFile_filePath = "" //* @input

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 1
    $QUEUE = "long"
}
//*
if (!((params.run_Single_Cell_Module && (params.run_Single_Cell_Module == "yes")) || !params.run_Single_Cell_Module)){
g108_85_bam_file_g108_78.into{g108_78_sorted_bam_g108_86}
g108_85_bam_index_g108_78.into{g108_78_bam_index_g108_86}
g108_78_count_file_g108_86 = Channel.empty()
} else {

process Single_Cell_Module_after_HISAT2_Count_cells {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_count.txt$/) "cell_counts_after_hisat2/$filename"
}

input:
 set val(oldname), file(sorted_bams) from g108_85_bam_file_g108_78
 set val(oldname), file(bams_index) from g108_85_bam_index_g108_78

output:
 set val(oldname), file("bam/*.bam")  into g108_78_sorted_bam_g108_86
 set val(oldname), file("bam/*.bam.bai")  into g108_78_bam_index_g108_86
 set val(oldname), file("*_count.txt")  into g108_78_count_file_g108_86

when:
(params.run_Single_Cell_Module && (params.run_Single_Cell_Module == "yes")) || !params.run_Single_Cell_Module

script:
"""
find  -name "*.bam" > filelist.txt
python ${params.countUniqueAlignedBarcodes_fromFile_filePath} -i filelist.txt -o ${oldname}_count.txt
mkdir bam
mv $sorted_bams bam/.
mv $bams_index bam/.
"""
}
}


params.filter_lowCountBC_bam_print_py_filePath = "" //* @input
cutoff_for_filter = 3000 //* @input @description:"script removes cells from bam files that are below cutoff value (eg. 3000 reads per cell)."
maxCellsForTmpFile = 500 //* @input @description:"maximum number of unique barcodes in each separated tmp file. Used for increasing performance of ESAT"

//* autofill
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 1
    $QUEUE = "long"
}
//*
process Single_Cell_Module_after_HISAT2_filter_lowCount {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /${name}_filtered_.*.bam$/) "filtered_bam_after_hisat2/$filename"
}

input:
 set val(oldname), file(sorted_bams) from g108_78_sorted_bam_g108_86
 set val(name), file(count_file) from g108_78_count_file_g108_86
 set val(oldname), file(bam_index) from g108_78_bam_index_g108_86

output:
 set val(name), file("${name}_filtered_*.bam")  into g108_86_filtered_bam_g108_87

"""
python ${params.filter_lowCountBC_bam_print_py_filePath} -i ${sorted_bams} -b ${name}_count.txt -o ${name}_filtered.bam -n ${cutoff_for_filter} -c ${maxCellsForTmpFile}
"""
}

esat_parameters = "-task score3p -wLen 100 -wOlap 50 -wExt 1000 -sigTest .01 -multimap ignore -scPrep" //* @input
params.ESAT_path = "" //* @input
params.gene_to_transcript_mapping_file = "" //* @input


//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 40
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 40
    $QUEUE = "long"
}
//* platform
//* autofill
process Single_Cell_Module_after_HISAT2_ESAT {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*.(txt|log)$/) "esat_after_hisat2/$filename"
	else if (filename =~ /.*umi.distributions.txt$/) "esat_after_hisat2/$filename"
}

input:
 set val(name), file(filtered_bam) from g108_86_filtered_bam_g108_87.transpose()

output:
 file "*.{txt,log}"  into g108_87_outputFileTxt
 set val(name), file("*umi.distributions.txt")  into g108_87_UMI_distributions_g108_88

script:
nameAll = filtered_bam.toString()
namePrefix = nameAll - ".bam"
"""    
find  -name "*.bam" | awk '{print "${namePrefix}\t"\$1 }' > ${namePrefix}_filelist.txt
java -Xmx40g -jar ${params.ESAT_path} -alignments ${namePrefix}_filelist.txt -out ${namePrefix}_esat.txt -geneMapping ${params.gene_to_transcript_mapping_file} ${esat_parameters}
mv scripture2.log ${namePrefix}_scripture2.log
"""
}

params.cleanLowEndUmis_path = "" //* @input

//* autofill
if ($HOSTNAME == "default"){
    $CPU  = 1
    $MEMORY = 20
}
//* platform
if ($HOSTNAME == "ghpcc06.umassrc.org"){
    $TIME = 500
    $CPU  = 1
    $MEMORY = 20
    $QUEUE = "long"
}
//* platform
//* autofill
process Single_Cell_Module_after_HISAT2_UMI_Trim {

publishDir params.outdir, overwrite: true, mode: 'copy',
	saveAs: {filename ->
	if (filename =~ /.*_umiClean.txt$/) "UMI_count_final_after_hisat2/$filename"
}

input:
 set val(name), file(umi_dist) from g108_87_UMI_distributions_g108_88.groupTuple()

output:
 set val(name), file("*_umiClean.txt")  into g108_88_UMI_clean

"""
cat ${umi_dist} > ${name}_merged_umi.distributions.txt
python ${params.cleanLowEndUmis_path} \
-i ${name}_merged_umi.distributions.txt \
-o ${name}_umiClean.txt \
-n 2
"""
}


process HISAT2_Module_HISAT2_Summary {

input:
 set val(name), file(alignSum) from g75_3_outputFileTxt_g75_2.groupTuple()

output:
 file "*.tsv"  into g75_2_outputFile_g75_10
 val "hisat2_alignment_sum"  into g75_2_name_g75_10

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
	if (filename =~ /${name}.tsv$/) "hisat2_alignment_summary/$filename"
}

input:
 file tsv from g75_2_outputFile_g75_10.collect()
 val outputFileName from g75_2_name_g75_10.collect()

output:
 file "${name}.tsv"  into g75_10_outputFileTSV

script:
name = outputFileName[0]
"""    
awk 'FNR==1 && NR!=1 {  getline; } 1 {print} ' *.tsv > ${name}.tsv
"""
}


workflow.onComplete {
println "##Pipeline execution summary##"
println "---------------------------"
println "##Completed at: $workflow.complete"
println "##Duration: ${workflow.duration}"
println "##Success: ${workflow.success ? 'OK' : 'failed' }"
println "##Exit status: ${workflow.exitStatus}"
}
