readMe.txt //for the whiteboard

*Fields needed in database in the routineTasks table:
name		type		description

id		int		task id which will be unique for each task created
title		varchar		title of the task
descr		longtext	the description of the task to be shown in the pop up window
creator		varchar		who created the task
createDate	date		when the task was created
editor		varchar		who edited the task last
editDate	date		when the task was last edited
timeDue		time		the hour and minute the task is due in 24 hour format
day		date		the specific day for one shot tasks null if not a oneshot
sun		int		1 or 0 based on if the task is to be done this day of the week
mon		int		1 or 0 based on if the task is to be done this day of the week
tue		int		1 or 0 based on if the task is to be done this day of the week
wed		int		1 or 0 based on if the task is to be done this day of the week
thur		int		1 or 0 based on if the task is to be done this day of the week
fri		int		1 or 0 based on if the task is to be done this day of the week
sat		int		1 or 0 based on if the task is to be done this day of the week
enabled		int		1 or 0 if this task is still relevant or can be logged and deleted
area		int		which area the task falls under ie. OPS or service desk

*Explanation of routineTasks table:
	This table will store the actual tasks themselves, it is purely for reference in populating
	the application with tasks to be completed. the descr field is for populating the popup window with more information about the task. the days of the week are for easily determining if a task needs to be done each day of the week or just one or more days in a week. if the day field is not NULL then the date in the day field is the ONE date the task should appear and would be catagorized normally as a one shot task. the "enabled" field is for our use as developers. If a task is enabled then it appears in the routine task list and should be done. When a task is no longer needed, it can be edited and the enabled field changed to 0. I am hoping to somehow implement an weekly or monthly sweep of this table to clear out and delete any "disabled" tasks and put them a log somewhere, much like we are doing with the witeboard.


*things needed in the routineTaskLog table:

name		type		description

id		int		just a tally of completed tasks
title		varchar		the title of the completed task for reference
taskId		int		the ID corresponding to the actual ID of the task in the routineTasks table
area		int		what area the task coresponds too
completed	int		1 or 0
completedBy	varchar		netID of who completed it
timeCompleted	time		the 24 hour time of when it was completed
dateCompleted	date		the date of when it was completed
comments	varchar		the comments added when the task was completed
muted		int		1 or 0
mutedBy		varchar		netID of who muted the task
timeMuted	time		the 24 hour time of when the task was muted
dateMuted	date		the date the task was muted
mutedComments	varchar		the comments added when the task was muted

*Explanation of the routineTaskLog table:
	This table is where the log of the completed and muted tasks go. It will store all of the information shown above. My thoughts about muting were, if a task gets muted, that obviously needs to be logged, and when it is an entry is added to the log table, and then when that task is completed, we can just compare the completed task against the muted tasks in the table that have not been completed and if it matches a task that has not been completed but HAS been muted we just update the completed fields for that table entry and we're done!  
	
	This log table could also be cleaned every month to delete any entries that have a date older than 30 days in the past, so basically on the first of every month clean out any entries that have a date older than last month (ie. in August clear our anything older than July etc etc)




*Basic structure for the routine task application:

index.php
	This file will house the code for the full application page. it will set up the HTML for the page
	and call the functions from the routineTaskTable.php to populate the tasks for the entire day
	with their current status and pertinent information. This page will also house links for creating
	new tasks and editing tasks for those who have that permission. There will also be the ability to
	select previous and next days and a link to return to the current days tasks.
	
	This page will also need functionality to update itself every so often so that the page does not
	need to be refreshed every time a task is completed.
	
	
	
routineTaskTable.php
	This file will house all of the functions neccessary to populate a routine task table, whether it
	is the full applicaion or just a few hours for a dashboard.
	
	It needs to be able to populate a list of tasks by specific day and order them in acsending order
	based on the time due. Since the tasks are stored in such a way that they are due on a specific
	day, it will need a way to determine what day of the week it is based on a date, unless that
	information is stored somehow in dates by the database.
	
	it will also need to be able to determine if a task has been completed or not and populate the 
	table accordingly for that task.
	
	it will pull its information from the two tables listed above. it will also include the files needed to check permissions and areas.
	
fullTask.php
	This file basically just outputs the HTML and information required when a task title is clicked on
	so that the user can see the description of the task in a popup window without leaving the 
	routine task list page.
	
createTask.php
	This file will house the code for creating new tasks and will insert them into the database. It will mostly just be a GUI for inputing information that will just go directly to the database.
	
editTask.php
	This will house the code required for editing a task, it will allow a user to edit all aspects of the task and will then update the database. the front end GUI will look almost identical to the createTask.php GUI but with small changes to reflect the status of editing a task and the ability to have fields already filled with the content of the task that is being edited.
	
More files may be added if necessary.
	

	
	