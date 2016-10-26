define(function () 
{
    'use strict';

    return function (target) 
    { 
    	var steps = target.steps;
    	
    	target.next = function() 
    	{
            var activeIndex = 0;
            steps.sort(this.sortItems).forEach(function(element, index) 
            {
                if (element.isVisible()) 
                {
                    element.isVisible(false);
                    activeIndex = index;
                }
            });
            
            if (steps().length > activeIndex + 1) 
            {
                var code = steps()[activeIndex + 1].code;
                
                /**
                 * Track checkout step
                 */
                AEC.checkoutStep(activeIndex, activeIndex + 1, code);
                
                steps()[activeIndex + 1].isVisible(true);
                window.location = window.checkoutConfig.checkoutUrl + "#" + code;
                document.body.scrollTop = document.documentElement.scrollTop = 0;
            }
        }
    	
    	return target;
    };
});