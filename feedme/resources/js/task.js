// Wrap logics in a function for recursive execution
function checkForTask(task, file)
{

    // Force task to run
    Craft.cp.setRunningTaskInfo({
        "id": task,
        "level": "0",
        "description": "Feeding " + file,
        "status": "running",
        "progress": 0
    });
    Craft.cp.trackTaskProgress();

    // Wait for jQuery to be ready
    $(function() {

        // Check if the taskicon is active
        if(!$('#taskicon').length) {
    
            // If not, check every 500ms if it is        
            setTimeout(function() {
                checkForTask(task, file);
            }, 500);
            
        } else {
        
            // If it is, open the task dialog
            $('#taskicon').trigger('click');
            
            // Run a task checker every 500ms
            var taskCheck = setInterval(function() {
            
                // Check if the task is done
                if((Craft.cp.runningTaskInfo && Craft.cp.runningTaskInfo.id != task) || Craft.cp.runningTaskInfo === null) {
                
                    // Clear task and redirect
                    clearInterval(taskCheck);
                    
                }
                
            }, 500);
            
        }
    
    });
    
}