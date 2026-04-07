#####################################################################################################
#  R Code to run "two-lines" test from own computer. 
#  For the paper "Two-Lines: A Valid Alternative to the Invalid Testing of U-Shaped Relationships with Quadratic Regressions"
#  Written by Uri Simonsohn (urisohn@gmail.com) - please contact me directly if you see any problems or have comments.
#  Paper on SSRN: https://papers.ssrn.com/sol3/papers.cfm?abstract_id=3021690
#  Last updated: 2018 04 02
######################################################################################



#Note: This example allows you to run the R Code for the two-lines app without needing to get into the nitty-gritty of knowing what does what.
#If you do want to look under the hood, change the functions, etc, all the code you need is the R file inside the "source()" below.


#1) Load the functions you need from the website (to see the code, just go to that URL)
    source("http://webstimate.org/twolines/twolines.R")   #<-----> Warning: This will install libraries the app relies on if you don't already have them.

#2) Load your data, here I load the example from the website
    data.example=read.csv("http://webstimate.org/twolines/example.csv")  

#3) Run analyses
    #3.1 Testing if x1 has u-shaped effect on y
        a=twolines(y~x1,data=data.example)
    
    #3.2 Testing if x2 has u-shaped effect on y
        a=twolines(y~x2,data=data.example)
      
    #3.3 Testing if x1  has u-shaped effect on y, controlling for x2
        a=twolines(y~x1+x2,data=data.example)