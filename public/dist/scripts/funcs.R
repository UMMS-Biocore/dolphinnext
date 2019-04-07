#To use any package in R, we need to load its library first.To run DESeq2 
#you just need to type the following.
library("DESeq2")
#We are going to use other libraries and lets load them too.
library("ggplot2")
library("RColorBrewer")
library("gplots")
options(warn=-1)
runDESeq <- function(data, columns, conds, padj=0.01, log2FoldChange=1, non_expressed_cutoff=10)
{
  data[, columns] <- apply(data[, columns], 2, function(x) as.integer(x))
  
  conds <- factor(conds)
  
  colData <- data.frame(colnames(data));
  colData <- cbind(colData, conds)
  colnames(colData) <- c("libname", "group")
  
  filtd <- subset(data, rowSums(data) > non_expressed_cutoff)
  
  dds <- DESeqDataSetFromMatrix(countData=as.matrix(filtd), colData=colData, design =~group)
  
  dds<-DESeq(dds)
  res<- results(dds)
  
  f1<- res[!is.na(res$log2FoldChange) & !is.na(res$padj), ]
  res_selected<- f1[f1$padj<padj & abs(f1$log2FoldChange)>log2FoldChange, ]
  de<-c()
  de$res_selected<-res_selected
  de$res_detected<-res
  de
}

overlaySig <- function (gdat, res_selected)
{
  #1. To Add a legend for all data we are going to add a text "Add" to 
  # whole values.
  Legend <- "All"
  gdat1 <- cbind(gdat, Legend)
  gdat_selected <- gdat[rownames(res_selected),]
  
  #2. Add a legend for only significant ones that we selected.
  Legend <- "Significant"
  gdat_selected1 <- cbind(gdat_selected, Legend)
  
  #3. We now need to merge selected and all data to draw them 
  # together on a plot with different colors.
  gdat2 <- rbind(gdat1, gdat_selected1)
  gdat2
}

volcanoPlot <- function (de,  padj=0.01, log2FoldChange=1, xlim=c(-2.5, 2.5), ylim=c(0, 15))
{
  de$res_detected$threshold = as.factor(abs(de$res_detected$log2FoldChange) > log2FoldChange & de$res_detected$padj < padj)
  de$res_detected$log10padj = -log10(de$res_detected$padj)
  dat<-data.frame(cbind(de$res_detected$log2FoldChange, de$res_detected$log10padj, de$res_detected$threshold))
  
  #Define your column names
  colnames(dat)<-c("log2FoldChange", "log10padj", "threshold")
  
  ##Construct the plot object
  ggplot(data=dat, aes_string(x="log2FoldChange", y="log10padj",
                              colour="threshold")) + geom_point(alpha=0.4, size=1.75) +
    theme(legend.position = "none") +
    xlim(xlim) + ylim(ylim) +
    xlab("log2 fold change") + ylab("-log10 p-value")
}

##getNormalized matrix
##input: numeric matrix
##output: numeric matrix with normalized counts
##requires edgeR package
getNormalizedMatrix <- function(M, method="TMM"){
  require(edgeR)
  norm.factors <- calcNormFactors(M, method = method)
  return(equalizeLibSizes(DGEList(M, norm.factors = norm.factors))$pseudo.counts)
}

runHeatmap <- function( data, filename )
{
  ld<- data
  cldt<- scale(t(ld), center=TRUE, scale=FALSE);
  cld<- t(cldt)
  dissimilarity<- 1 - cor(cld)
  distance<- as.dist(dissimilarity)
  pdf(filename, 7, 7)
  plot(hclust(distance), main="Dissimilarity = 1 -Correlation", xlab="")
  hclust2 <- function(x, ...)
    hclust(x, ...)
  dist2 <- function(x, ...)
    as.dist(1-cor(t(x)))
  
  heatmap.2(cld, Rowv=TRUE,dendrogram="column", Colv= TRUE, col=redblue(256), labRow=NA,
            distfun=dist2, hclustfun=hclust2,
            density.info="none", trace="none")
  dev.off()
}

all2all <- function(data)
{
  pairs(log10(data[1:1000,1:(dim(data)[2])]), pch=19, cex=0.25, diag.panel= panel.hist, lower.panel=panel.cor)
}


## Change the bottom half. Do not repeat the graphs. 
panel.hist <- function(x, ...)
{
  usr <- par("usr"); on.exit(par(usr))
  par(usr = c(usr[1:2], 0, 1.5) )
  h <- hist(x, plot = FALSE)
  breaks <- h$breaks; nB <- length(breaks)
  y <- h$counts; y <- y/max(y)
  rect(breaks[-nB], 0, breaks[-1], y, col="red", ...)
}

panel.cor <- function(x,y, ...)
{
  par(new=TRUE)
  cor_val <- cor.test(x, y, method="spearman", na.rm=T)$estimate
  cor_val2 <- cor.test(x, y, method="pearson", na.rm=T)$estimate
  cor_val <- round(cor_val, digits=2)
  cor_val2 <- round(cor_val2, digits=2)
  #legend("center", cex=0.75, bty="n", paste("rho=",cor_val, "\nr=", cor_val2))
  legend("center", cex=0.75, bty="n", paste("rho=",cor_val))
}

panel.smoothScatter <- function (x, y, ...) 
{
  par(new=TRUE)
  smoothScatter(x, y, nrpoints=0)
}

run_pca <- function(x, retx = TRUE, center = TRUE, scale = TRUE) {
  
  pca <- prcomp(t(x), retx = retx, center = center, scale. = scale)
  variances <- pca$sdev^2
  explained <- variances / sum(variances)
  return(list(PCs = pca$x, explained = explained))
}

# Plot PCA results.
#

plot_pca <- function(x, pcx = 1, pcy = 2, explained = NULL, metadata = NULL,
                     color = NULL, shape = NULL, size = NULL, factors = NULL) {
  library("ggplot2")
  
  # Prepare data frame to pass to ggplot
  if (!is.null(metadata)) {
    
    plot_data <- cbind(x, metadata)
    plot_data <- as.data.frame(plot_data)
    # Convert numeric factors to class "factor"
    for (f in factors) {
      plot_data[, f] <- as.factor(plot_data[, f])
    }
  } else {
    plot_data <- as.data.frame(x)
  }
  # Prepare axis labels
  if (!is.null(explained)) {
    xaxis <- sprintf("PC%d (%.2f%%)", pcx, round(explained[pcx] * 100, 2))
    yaxis <- sprintf("PC%d (%.2f%%)", pcy, round(explained[pcy] * 100, 2))
  } else {
    xaxis <- paste0("PC", pcx)
    yaxis <- paste0("PC", pcy)
  }
  # Plot
  p <- ggplot(plot_data, aes_string(x = paste0("PC", pcx),
                                    y = paste0("PC", pcy))) +
    geom_point(aes_string(color = color, shape = shape, size = size)) +
    labs(x = xaxis, y = yaxis)
  p
}


