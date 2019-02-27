Question type sassessment
----------------------

A simple question type sassessment.

It is a copy of the shortanswer question type with everything unnecessary removed and :

* countback grading
* hints
* question text
* no grading implemented at all
* no input controls at all in question as yet
* it doesn't install any new tables



###Installation
You can keep a copy of the sassessment in Moodle in the question/type/ folder and as long as it is called sassessment the plug in will
be ignored.
Rename folder to "sassessment"


####Installation Using Git 

To install using git for the latest version (the master branch), type this command in the
root of your Moodle install:

    git clone git://github.com/e-rasvet/moodle-qtype_sassessment.git question/type/sassessment
    echo '/question/type/sassessment' >> .git/info/exclude

####Installation From Downloaded zip file

Alternatively, download the zip from :

* latest (master branch) - https://github.com/e-rasvet/moodle-qtype_sassessment/zipball/master

unzip it into the question/type folder, and then rename the new folder to sassessment.

####Doesn't get installed as long as it is called sassessment

You can keep a copy of the sassessment in Moodle in the question/type/ folder and as long as it is called sassessment the plug in will
be ignored.


@copyright  Paul Daniels, Igor Nikulin