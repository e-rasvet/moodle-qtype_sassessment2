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

###Who should use


This is one alternative start for devloping a question type plug in and is working code as is. Although it doesn't do any actual
grading or collect student input at all.

Depending on what type of question plug in you want to develope it might be good to either :

* use one of the existing question types that is doing something similar to what you want to do as a base, copy that,
have fun deleting no longer needed code and you then have a sassessment to start from.
* or if possible to avoid code duplication it is better to extend existing classes, particularly for the question type and
question classes. There are quite a few examples of queston types that do this at https://github.com/moodleou/.
        for example classes in ddimageortext and ddmarker both inherit from common code in ddimageortext and those inherit code from the gapselect question type
* or this code might help start you off.


###Installation

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