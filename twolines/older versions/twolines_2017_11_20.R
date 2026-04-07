#This version: 2017 11 20
#Two-lines App 0.2
  rm(list=ls())  # clean it all
  .libPaths("/home/urisoh5/R/lib")  #Chnge to location of library

  library(mgcv)         #This library has the additive model with smoothingfunction 
  library(stringr)      #To process strings in the function
  library(sandwich)     #For robust standard errors
  library(lmtest)       #To run linear tests
  
  
  #OUTLINE
    #1 Function 1 - modify_s_due_few_x()   Modifies a formula including the term s(x) so that the degree of the spline being fit is no higher than that of unique values of x
    #2 Function 2 - rp(p)                  Reformat p-value for printing on chart
    #3 Function 3 - process.f(I)           Check that formula entered into function is ok, and convert to string list
    #4 Function 4 - reg2()                 Interrupted regression with entered formula plus cutoff point
        
    
  
  
############################
#Function 1 - Modifies a formula including the term s(x) so that the degree of the spline being fit is no higher than that of unique values of x
    modify_s_due_few_x=function(str)  {  
      #Syntax
      # str=s(x)
            
          #Explanation.
          #  This function modifies the s() command to avoid an error due to few observed values.
          #
          #  EXAMPLE OF PROBLEM
          #     x=rep(c(11,22,33),100)
          #     y=2*x^2+rnorm(300)
          #     gam(y~s(x))
          #
          #     Results in an error becuase there are too few possible values for the default degree of the spline
          #     But this command does not:
          #     gam(y~s(x,bs='cr', k=3))
          #     So modify_s_due_few_x() is a function that adds the option to avoid the error
          #     Note: i use bs='cr', cubic splines, to ehance compatibility with non-R spline programs that rely on cubic splines
            
    #Parse str into vector separted by +
      t1=unlist(strsplit(str,"\\+"))
      t2=t1          #Vector with modified textg
      i=1            #Start counter of elements
      #Loop over individual variables in the statement, looking for s()
      for (tx in t1) {
        #Is it a s()?
        if (str_sub(tx,1,2)=="s(" )
            {
            #Grab the variable itself
              var=substring(tx, 3, nchar(tx)-1)  
            #Count how many unique values it has
              unique.var=length(unique(eval(as.name(var))))
            #Create robust s() statement based on it
              t2[i]=paste0("s(",var,",bs='cr', k=min(10,",unique.var,"))" )
            }#End if s() statement
            i=i+1
          }#end for
      #Output results
        paste(t2,sep="+",collapse="+")
      }
      

#Function 2 - Reformat p-value for printing on chart
      rp=function(p) if (p<.0001)  return("p<.0001")  else  return(paste0("p=",format(round(p,3),nsmal=3)))

    
#Function 3 - Check that formula entered into function is ok, and convert to string list
    process.f=function(f) 
    {
    #POSSIBLE ERROR MESSAGES:
    #Error messages
    #MSG 1:  More than 1 u() in the formula
        msg.toomany=c("\nYou may test one u-shape pattern at a time.\n",
                  "If more than one variable is hypothesized to be u-shaped it is best to\n",
                  "do each variable at a time controlling for the others with a smoother.\n", 
                  "For example, say y~x1+x2+x3 and both x1 and x2 could be u-shaped.\n",
                  "We run:\n",
                  "testu(y~u(x1)+s(x2)+x3)\ntesting whether x1 on y is u-shaped with a flexible functional for on x2, and then vice-versa\n",
                  "testu(y~s(x1)+u(x2)+x3).")
    #MSG 2:  No u() in the formula
        msg.toofew=c("You must indicate which predictor to test u-shape for, by putting it in inside a u() like this: y~u(x1)+x2+x3.")
    ########################################################################  
          
    #(1) Validate 
      #1.1 Turn f into a string
          str=format(f)
          str=str_replace_all(str," ","")
      #1.2 see how often u() rappears, error if !=1
           u.total=str_count(str,"u\\(" )  #This counts the number of times u( appears. It has \\( to escape ( as a regular expression
          if (u.total==0) stop (paste(msg.toofew))
          if (u.total> 1) stop (paste(msg.toomany))

      #1.2 Make sure u is not a variable
           u.var.count=str_count(str,"u\\+" )              
           u.var.count=u.var.count+str_count(str,"\\+u\\+" )  
           if (u.var.count>0) stop ("Please do not use variables named 'u'.")
        
    #(2) Extract parts 
        #2.1 all variables
          varnames=all.vars(f)    #all.vars() is base:: function. It takes all variables in a formula and puts them on a vector
          vartot=length(varnames)
              
        #2.2 all elements
          f.parts=all.vars(f,functions=TRUE)   #This includes the ~, s, u() etc
        #2.3  y as a string
          str.y=varnames[1]
        #2.4  u(x)
          #2.4.1 Position of "u" within formula, to identify two-lines variables
            uk=match("u",f.parts)
          #2.4.2 Get following element in all()
            str.u=f.parts[uk+1]
              
     #(3) Convert formula replacing u() with s() and making s() robust to discrete
        #3.1 extract RHS (becuase the modify_s_due_few_x() works with strings without LHS)
          str.s=str_sub(str,str_locate(str,"~")[1]+1,-1)[1]
        #3.2 - replace u() with  s()
          str.s=str_replace(str.s,"u\\(","s\\(")
        #3.3 Make the s() robust to discrete values by adding k=min(10,unique(x))
          str.s=modify_s_due_few_x(str.s)
        #3.4 Add back the LHS
          str.s=paste0(str.y,"~",str.s)
        
      #(4) String with full formula except for u(x)
        #4.1 Start with full formula in string
          RHS=f.parts
        #4.2 now drop dv, ~ and u(x), what's left are the other predictors, if any
          RHS=RHS[RHS!="~"]
          RHS=RHS[RHS!="+"]
          RHS=RHS[RHS!="u"]
          RHS=RHS[RHS!=str.y]
          RHS=RHS[RHS!=str.u]
          RHS=paste(RHS,collapse="+")  #put all the ones tha tremain together in string with + in between 
          
      
      #(5) Results
           res=list(varnames=varnames, vartot=vartot, u=str.u, s=str.s, 
                    y=str.y, RHS.nou=RHS)
          res
              #str$varnames  vector with all variable names (including y and u(x))
              #str$vartot:   total number of variables, include y and u(x)
              #str$y:        the dv
              #str$s:        the formula replacing u(x) with s(x) for the smoothed fitting
              #str$x:name of u(x) variable x
              #str$RHS.nou        :      the RHS without the u() to use as covariates in two-lines
        }
      
   
  
      
  #Function 4 - two-line regression with gam
      reg2=function(f,xc,data=NULL,graph=1,family="gaussian")
      {
        #Syntax: 
        #f: formula as in y~u(x1)+x2+s(x3)
        #xc: where to set the breakpint, a number
        #link: 
        #       Gaussian for OLS 
        #       binomial for probit
        #       see mangual for gvsm:gam() for more
               

      #(11) Turn f to string, and generate list with all necessary elements (str.y, str.u, etc)
            str=process.f(f)  #format(f) turns the formula onto a string and process.f generates list with components (see function abnove)

      #(2) Generate new variables for two lines
            #New variables
            #3.1 Make x
                  x=eval(as.name(str$u))          #x is variable of interst
                  
            #3.3 For xc to be between 10th and 90th percentile
                  xc=min(xc,quantile(x,.9))
                  xc=max(xc,quantile(x,.1))
                  
            #3.2 xc is included in the first line
                  xlow1=ifelse(x<=xc,x-xc,0)     #xlow=x-xc when x<xc, 0 otherwise, use  to avoid conflict with user variables
                  xhigh1=ifelse(x>xc,x-xc,0)     #xhigh=x when x<xmax, 0 otherwise, use  to avoid conflict with user variables
                  high1=ifelse(x>xc,1,0)         #high dummy, allows interruption, use  to avoid conflict with user variables
            #3.4 Now include xc in second line
                  xlow2=ifelse(x<xc,x-xc,0)     
                  xhigh2=ifelse(x>=xc,x-xc,0)     
                  high2=ifelse(x>=xc,1,0)         #high dummy, allows interruption
                  
            #3.5 Start with strings for linear model
                  str$lm1=paste0("xlow1+xhigh1+high1")
                  str$lm2=paste0("xlow2+xhigh2+high2")
                  
            #3.6 If covariates, add them to str.lm
                  if (str$vartot>2)
                    {
                    str$lm1=paste0(str$lm1,"+",str$RHS.nou)
                    str$lm2=paste0(str$lm2,"+",str$RHS.nou)
                    }
                  
            #3.7 Make str.lm robust to discrete xs by adding that it be a cubic spline and k=max(unique values) where k=10 is default and unique values<10 is accomodated with command
                  str$lm1=modify_s_due_few_x(str$lm1)
                  str$lm2=modify_s_due_few_x(str$lm2)
              
            #3.8 add "y~" at beggining
                  str$lm1=paste0(str$y,"~",str$lm1)
                  str$lm2=paste0(str$y,"~",str$lm2)
                  
         #(4) Run interrupted regressions
            #4.1 Estimation itself (they differe only on whether xc is 'high' or 'low')
                  glm1=glm(as.formula(str$lm1),data=data, family = family)
                  glm2=glm(as.formula(str$lm2),data=data, family = family)

            #4.2 Compute robust standard errors
                  rob1=coeftest(glm1, vcov=vcovHC(glm1,"HC3"))
                  rob2=coeftest(glm2, vcov=vcovHC(glm2,"HC3"))
                  
            #4.3 Slopes
                b1=as.numeric(rob1[2,1])
                b2=as.numeric(rob2[3,1])
            
              #4.4 Test statistics, z-values 
                z1=as.numeric(rob1[2,3])
                z2=as.numeric(rob2[3,3])
            #4.5 p-values
                p1=as.numeric(rob1[2,4])
                p2=as.numeric(rob2[3,4])
            
          
          #5) Is the u-shape significant?
                u.sig =ifelse(b1*b2<0 & p1<.05 & p2<.05,1,0)                     
          
          #6) Plot results)
                if (graph==1) {
                  
          #6.1 General colors and parameters
                pch.dot=1          #Dot for scatterplot (data)
                col.l1='blue2'     #Color of straight line 1
                col.l2='red3'      #Color of straight line 2
                col.fit='gray70'    #Color of fitted smooth line
                col.dot='gray60'   #Color of dots
                col.div="green3"   #Color of vertical line
                lty.l1=1           #Type of line 1
                lty.l2=1           #Type of line 2
                lty.fit=2          #Type of smoothed line
        
          #6.2) Estimate smoother 
                gams=gam(as.formula(str$s),link=link)
                
          #Get dots 
          #6.3) If no covariates, yobs is the actually observed data
                  y=eval(as.name(str$y))
                  if (str$vartot==2) yobs=y
          
          #6.4) If covariates present, yobs is the fitted value with u(x) at mean, need new.data() with variables at means
                  if (str$vartot>2) {
                           
            #6.4.1) Put observed data into matrix
                    data.obs=as.data.frame(matrix(nrow=length(eval(as.name(str$u))),ncol=length(str$varnames)))
                    colnames(data.obs)=str$varnames
            #6.4.2 Populate the matrix with the variables
                    for (i in 1:length(str$varnames)) data.obs[,i]=eval(as.name(str$varnames[i])) 
            #6.4.3) Drop observations with missing values on any of the variables
                    data.obs=na.omit(data.obs)
            #6.4.4) Create data where u(x) is at sample means to get residuals based on rest of models to act as yobs
                    #Recall: columns 1 & 2 have y and u(x) in obs.data
                        data.xufixed    =data.obs  
                        k=match(str$u,str$varnames)                 #Column with u(x)
                        data.xufixed[,k]=mean(data.xufixed[,k])     #Make that column equal to its mean for prediction
            #6.4.5) Create data where u(x) is obs, and all else at sample means
                        data.otherfixed              = data.obs     #start with original value
                    #Replace all RHS with mean, except the u(x)
                        for (i in 2:str$vartot) if (i!=k) data.otherfixed[,i]=mean(data.obs[,i])  

          #6.4.6) Get yobs with covariates
                  #First the fitted value
                    yhat.xufixed=predict.gam(gams,newdata = data.xufixed)
                  #Substract fitted value from observed y, and shift it with constant so that it has same mean as original y
                    yobs = y-yhat.xufixed
                    yobs=yobs+mean(y)-mean(yobs)  #Adjust to have the same mean
                  } #End if for covariates that requires computes y.obs instead of using real y.
                
            #6.5) First line (x,y) coordinates
                    offset1=mean(yobs[x<=xc])-min(x)*b1-(xc-min(x))/2*b1
                    x.l1=c(min(x),xc)
                    y.l1=c(min(x)*b1+offset1,xc*b1+offset1)
                    
            #6.6) First line (x,y) coordinates
                    offset2=mean(yobs[x>=xc])-xc*b2-(max(x)-xc)/2*b2
                    x.l2=c(xc,max(x))
                    y.l2=c(xc*b2+offset2,max(x)*b2+offset2)

            #6.7) Get yhat.smooth
                    #Without covariates, just fit the observed data
                        if (str$vartot==2) yhat.smooth=predict.gam(gams) 
                    #With covariates, fit at observed means
                        if (str$vartot>2)  yhat.smooth=predict.gam(gams,newdata = data.otherfixed)
                  #Substract fitted value from observed y
                        offset3 = mean(yobs-yhat.smooth)
                        yhat.smooth=yhat.smooth+offset3
                  
            #6.8) Coordinates for top and bottom end of chart
                y1   =max(yobs,y.l1,y.l2,yhat.smooth)  #highest point
                y0   =min(yobs,y.l1,y.l2,yhat.smooth)  #lowest point
                yr   =y1-y0                            #range
                
             #xs
                x1   =max(x)  
                x0   =min(x)  
                xr   =x1-x0                              

            #Share of data in each quadrant
                q1=sum(x>(x0+.25*xr) &  x<(x1-.75*xr) & y<(y0+.25*yr) )
                q2=sum(x>(x0+.25*xr) &  x<(x1-.75*xr) & y>(y1-.25*yr) )
                
                if (q1<=q2) {
                    legend.pos="bottom"
                    y0=y0-.3*yr
                }
                
                if (q1>q2) {
                    legend.pos="top" 
                    y1=y1+.3*yr
                }
                
          #6.10) Plot dots
            plot(x,yobs,cex=.75,col='gray75',pch=pch.dot,las=1,
                   ylim=c(y0,y1),
                   xlab="",
                   ylab="")  #Range of y has extra 30% to add labels
            
            #Axis labels
              mtext(side=1,line=2.75,paste0(str$u))
              mtext(side=2,line=2.75,str$y,las=1)
              
            

          #6.12) Smoothed line
            lines(x[order(x)],yhat.smooth[order(x)],col=col.fit,lwd=2,lty=lty.fit)
            
          #6.13) Straight line 1
                lines(x.l1,y.l1,type='l',
                      lty=lty.l1,
                      xlim=c(min(x),max(x)),
                      col=col.l1,
                      lwd=2)
          #6.14) Straight line 2
               lines(x.l2,y.l2,type='l',lty=lty.l2, col=col.l2,lwd=2)
            
            #6.15
               #6.17 Division line
               lines(c(xc,xc),c(y0+.1*yr,y1-.1*yr),col=col.div,lty=lty.fit)
               
               
          #6.16) Legend
            #6.16.1) Text for lines 1 & 2
              text.l1=paste0("Line 1: b=",round(b1,2),", z=",round(z1,2),", ",rp(p1))
              text.l2=paste0("Line 2: b=",round(b2,2),", z=",round(z2,2),", ",rp(p2))
            
            ##6.16.2) Text for data
                #Label for data when no covariates
                  if (str$vartot==2) data.legend="Observed data"
                #Label for data when there are covariates
                  if (str$vartot>2) data.legend=paste0("Observed data (controlling for covariates)")
            
            #6.16.3) Legend itself
                legend(legend.pos,inset=.02,
                    c(data.legend,'Smoothed scatterplot',text.l1, text.l2),
                    pch=c(pch.dot,NA,NA,NA),
                    lty=c(NA,lty.fit,lty.l1,lty.l2),
                    lwd=1,
                    bty='gray',
                    cex=.75,
                    col=c(col.dot,col.fit,col.l1,col.l2))
             
             if (legend.pos=="top")    text(xc,y0,round(xc,2),col=col.div,cex=1)
             if (legend.pos=="bottom") text(xc,y1,round(xc,2),col=col.div,cex=1)
        }#End: if  graph==1
      
      #7 list with results
          res=list(b1=b1,p1=p1,b2=b2,p2=p2,u.sig=u.sig,xc=xc,z1=z1,z2=z2,
                   glm1=rob1,glm2=rob2)  #Output list with all those parameters, betas, z-values, p-values and significance for u
        #output it      
          res
      }  #End of reg2() function
  
  
  #Function 6- 
        twolines=function(f,graph=1,link="gaussian",data=NULL,pngfile="")  {
      #(1) Setup
        #1.1 Furmula-->string
           str=process.f(f)  #format(f) turns the formula onto a string and process.f generates list with components (see function abnove)
        
        #1.2 the x variable in u(x)
           x=eval(as.name(str$u))          #COnvert it into a vector
        
      #(2) Generate yobs (dots)
      #2.1 If no covariates, yobs is the actually observed data
            if (str$vartot==2) yobs=eval(as.name(str$y))
      
      #2.2 If covariates present, yobs is the fitted value with u(x) at mean, need new.data() with variables at means
            if (str$vartot>2) {
               
      #2.3 Put observed data into matrix
              data.obs=as.data.frame(matrix(nrow=length(eval(as.name(str$u))),ncol=length(str$varnames)))
              colnames(data.obs)=str$varnames
      #2.4 Populate the matrix with the variables
              for (i in 1:length(str$varnames)) data.obs[,i]=eval(as.name(str$varnames[i])) 
      #2.5 Drop observations with missing values on any of the variables
              data.obs=na.omit(data.obs)
      #2.6 Create data where u(x) is at sample means to get residuals based on rest of models to act as yobs
          #Recall: columns 1 & 2 have y and u(x) in obs.data
              data.xufixed    =data.obs  
              k=match(str$u,str$varnames)                 #Column with u(x)
              data.xufixed[,k]=mean(data.xufixed[,k])     #Make that column equal to its mean for prediction
     
      #2.7 Get yobs with covariates
            #First the fitted value
              gams=gam(as.formula(str$s))
              yhat.xufixed=predict.gam(gams,newdata = data.xufixed)
            #Substract fitted value from observed y
              yobs = y-yhat.xufixed

     #2.8 Create data where u(x) is obs, and all else at sample means
              data.otherfixed              = data.obs     #start with original value
           #Replace all RHS with mean, except the u(x)
              for (i in 2:str$vartot) if (i!=k) data.otherfixed[,i]=mean(data.obs[,i])  
            } #End if covariates are present to compute yobs 
           
      #3) Get yhat (with covariates at sample means, by relying on data.otherfixed)
      #3.1) Run smooth gam() at s()
            gams=gam(as.formula(str$s))
      #3.2) Get the fitted values
            #3.2.1) Get predicted values into list
            if (str$vartot>2)  g.fit=predict.gam(gams,newdata = data.otherfixed,se.fit=TRUE)
            if (str$vartot==2) g.fit=predict.gam(gams,se.fit=TRUE)
           
            #3.2.2) Take out the fitted itself
              y.hat=g.fit$fit
            #3.2.3) Now the SE
              y.se =g.fit$se.fit
            
              
      #4) Most extreme fitted value
        #4.0 Determine if function is at first decreasing (potential u-shape)  vs. increaseing (potentially inverted U)  (potential u-shape) orinverted u shaped using quadratic regression
            
            str$lmq=paste0(str$y,"~",str$u,'+I(',str$u,'^2)')  #Formula for the quadratic
            if (str$vartot>2) str$lmq=paste0(str$lmq,"+",str$RHS.nou)
            str$lmq=as.formula(str$lmq)
            
            lmq=lm(str$lmq)                #Estimate the quadratic regression
            bqs=lmq$coefficients           #Get the point estimates
            bx1= bqs[2]                    #point estimate for effect of x
            bx2=bqs[3]                     #point estimate for effect of x^2
            x0=min(eval(as.name(str$u)))   #lowest x-value
            s0=bx1+2*bx2*x0             #estimated slope at the lowest x-value
            
            if (s0>0)  shape='inv-ushape'   #if the quadratic is increasing at the lowest point, the could be inverted u-shape
            if (s0<=0) shape='ushape'       #if it is decreaseing, then it could be a regular u-shape
            
            
        #4.1 Get the middle 80% of data to avoid an extreme cutoff
            x10=quantile(x,.1)
            x90=quantile(x,.9)
            middle=(x>x10 & x<x90)       #Don't consider extreme values for cutoff
            x.middle=x[middle]       
            
        #4.2 Restrict y.hat to middle    
            y.hat=y.hat[middle]
            y.se=y.se[middle]

          #4.3 Find upper and lower band
            y.ub=y.hat+y.se            #+SE is for flat max
            y.lb=y.hat-y.se            #-SE is for flat min
        
        #4.4 Find most extreme y-hat
            if (shape=='inv-ushape') y.most=max(y.hat)   #if potentially inverted u-shape, use the highest y-hat as the most extrme
            if (shape=='ushape')     y.most=min(y.hat)   #if potential u-shaped, then the lowest instead
            
        #4.5 x-value associated with the most extreme value
            x.most=x.middle[match(y.most, y.hat)]       
  
        #4.6 Find flat regions
          if (shape=='inv-ushape') flat=(y.ub>y.most)
          if (shape=='ushape')     flat=(y.lb<y.most) 
          xflat=x.middle[flat]
          
    #5 RUN TWO LINE REGRESSIONS
      #(5.1) Midpoint regression
          rmid=reg2(f=f,xc=median(xflat),graph=0)  #Two line regression at the median point of flat maximum
          
      #(5.2) Extract Get z1 and z2 for the max and min flat area midpoints
        z1=abs(rmid$z1)             
        z2=abs(rmid$z2)             

     #(5.3) Adjust breakpoint based on z1,z2
        xc=quantile(xflat,z2/(z1+z2))  
        
     #(5.4) Regression split based on adjusted based on z1,z2    
         #Save to png?
            if (pngfile!="") png(pngfile, width=2000,height=1500,res=300) 
			#Run the two lines
				res=reg2(f,xc=xc,graph=graph)
          #Save to png? (close)
            if (pngfile!="") dev.off()
          
        
      #(5.5)Add other results obtained before
        res$yobs       = yobs
        res$y.hat      = y.hat
        res$y.ub       = y.ub
        res$y.lb       = y.lb
        res$y.most     = y.most
        res$x.most     = x.most
        res$f          = f
        res$xvar       = str$u     #this is the variable predicted to have a ushaped effect
        res$yvar       = str$y 
        res$bx1        = bx1     #linear effect in quadratic regressino
        res$bx2        = bx2     #quadratic
        res$minx       = min(x)   #lowest x value
        res$midflat    = median(xflat)
        res$midz1      = abs(rmid$z1)
        res$midz2      = abs(rmid$z2)
        res
		} #End function
        
############################################
      
    
        