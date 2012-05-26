#!/bin/sh

clear

echo updating from svn
echo =================
git svn rebase
echo
echo pushing to github
echo =================
git push origin master

exit



# couldn't get it to work
#cd /files/work/javaworkspace/WebAuctionPlus-togithub && svn2git --rebase --verbose
#svn2git http://webauctionplus.googlecode.com/svn --authors=/files/work/javaworkspace/githubauthors.txt --verbose --revision=1

#git svn clone -s http://webauctionplus.googlecode.com/svn /files/work/javaworkspace/WebAuctionPlus-togithub
#git remote add origin git@github.com:lorenzop/WebAuctionPlus.git

# to test connection
#ssh -vT git@github.com
#
#git push origin master

# update fork
#git svn fetch - not needed
#git pull -r upstream master
#git push origin master

