<?php
class CalendarHelper extends AppHelper{
    var $helpers = array('Html','Javascript');


    /**
     *
     * @var string strftime format for the day abbreviation.  Used in column headers
     */
    public $day_abbreviation_format = '%a';
    /**
     *
     * @var string tag to be used to contain the day within the table cell
     */
    public $day_tag = 'span';
    /**
     *
     * @var string name of the class that contains the day
     */
    public $day_class = 'day';
    /**
     *
     * @var string class to append to the cell if displayed day is today
     */
    public $today_day_class = 'today';
    /**
     *
     * @var string class to append to td cell if there is no date to show 
     */
    public $empty_day_class = 'empty-day';
    /**
     *
     * @var string class to append to td cell if that day is the selected day by named params
     */
    public $selected_class = 'selected';

    /**
     *
     * @var string class of prev / next month displayed in header
     */
    public $next_prev_month_class = 'next-prev-month';

    /**
     *
     * @var string tag to contain individual pieces of content injected into each day
     */
    public $day_content_tag = 'div';

    /**
     *
     * @var string class name to use in day_content_tag
     */
    public $day_content_class = 'day-content';

    /**
     * @var string class name of the calendar container
     */
    public $container_class = 'calendar';

    /**
     *
     * @var string id of the calendar container
     */
    public $container_id = null;

  

/**
     * Builds a calendar
     *
     * @param array $options Array containing the following indeces:
     *  month (int)
     *  year(int)
     *  next_prev_count (int)  number of months to show to the left and right for navigation
     *  content array - year / month / day indexed, the index should represent the month => day in which you want the content appended
     *          (ex) For the month of June, in 2010 array( 2010 => array(06 => array(1 => 'Partridge in a pear tree', 5 => 'Golden Rings')))
     * @return string output of an HTML calendar
     * @access public
     */
    function draw($options = array()){
    	$options = $this->_setDefaults($options);


        $today = date('d');
        //what's the first day of this month?
        $first_day = date('w',mktime(0,0,0,$options['month'],1,$options['year']));
        //how many days are in this month?
        $days_in_month = date("t",mktime(0,0,0,$options['month'],1,$options['year']));
        //how long are we looping?
        $weeks_in_month = ceil(($first_day + $days_in_month) / 7);
        $day_counter = 1;


        //Main content var to be returned
        $calendar_content = '';

        $view =& ClassRegistry::getObject('view');

        //We will start by building out the year and month header 


        //Any "previous" month navigations we wanna show?
        for($x=(0-$options['next_prev_count']);$x < 0;$x++){
            $month_year = $this->__getYearAndMonth($options['month'],$options['year'],$x);
            //@TODO place these in separate element as a step towards putting more items in the calendar as re-usable and easier to manage display elements
            $calendar_content .= $this->Html->link(strftime('%b',mktime(0,0,0,$month_year['month'],1,$month_year['year'])),
	                array_merge($this->params['named'],$month_year),
	                array(
	                    'title' => __('Previous Month',true),
	                    'id' => 'previous-month',
                            'class' => $this->next_prev_month_class
	            ));
            

        }
        //Show the current month / year in between previous and next months
        $calendar_content .= $this->Html->tag('span',strftime('%B',mktime(0,0,0,$options['month'],1,$options['year'])),array('id' => 'calendar-month'));
        $calendar_content .= $this->Html->tag('span',strftime('%Y',mktime(0,0,0,$options['month'],1,$options['year'])),array('id' => 'calendar-year'));

        //Next Months
        for($x=0;$x<$options['next_prev_count'];$x++){
            $month_year = $this->__getYearAndMonth($options['month'],$options['year'],$x+1);
            $calendar_content .=  $this->Html->link(strftime('%b',mktime(0,0,0,$month_year['month'],1,$month_year['year'])),
	                array_merge($this->params['named'],$month_year),
	                array(
	                    'title' => __('Next Month',true),
	                    'id' => 'next-month',
                            'class' => $this->next_prev_month_class
	            ));
        }

        $days_of_week = $this->days_of_week();
        $calendar_table_header = $this->Html->tableHeaders($days_of_week);

        $table_rows = array();
	       
	            
        for($x=0;$x<$weeks_in_month;$x++){
            $table_cells = array();
            for($y=0;$y<7;$y++){
                $cur_day = $day_counter - $first_day;
                if( ($cur_day >= 1)  && ($cur_day <= $days_in_month)){

                    if($options['show_day_link'] == true){
                        $options['link_template']['day'] = $cur_day;
                        
                        $display_day_value = $this->Html->link($cur_day,$options['link_template']);
                    }else{
                            $display_day_value = $cur_day;
                    }
                }else{//this day is not in the month, so we show nothing
                    $display_day_value = " ";
                }

                $td_class_name = '';
                if($today == $cur_day && $options['month'] == date('m') && $options['year'] == date('Y')){
                    $td_class_name = $this->today_day_class;
                }elseif(isset($this->params['named']['day']) && $cur_day == $this->params['named']['day']){
                    $td_class_name = $this->selected_day_class;
                }elseif($display_day_value == " "){
                    $td_class_name = $this->empty_day_class;
                }

                $cell_content = $this->Html->tag('span',$display_day_value,array('class' => $this->day_class));
                if(isset($options['content'][$options['year']][$options['month']][$cur_day])){
                    if(!is_array($options['content'][$options['year']][$options['month']][$cur_day])){
                        $options['content'][$options['year']][$options['month']][$cur_day] = array($options['content'][$options['year']][$options['month']][$cur_day]);                         
                    }
                    foreach($options['content'][$options['year']][$options['month']][$cur_day] as $day_content){
                        $cell_content .=  $this->Html->tag($this->day_content_tag,$day_content,array('class' => $this->day_content_class));
                    }
                    
                }
                $table_cells[] = array($cell_content,array('class' => $td_class_name));
                
                $day_counter++;
            }//End loop -- days this week
            $table_rows[] = $table_cells;
        }//End loop -- weeks in month
        
        $calendar_table_cells = $this->Html->tableCells($table_rows);
        $calendar_content .= $this->Html->tag('table',$calendar_table_header . $calendar_table_cells);
        $calendar_content = $this->Html->tag('div',$calendar_content,array('class' => $this->container_class,'id' => $this->container_id));
        return $calendar_content;
    }



    private function __getYearAndMonth($month,$year,$monthIteration = 0){
        $month += $monthIteration;
        $monthYearArray = array(
            'month' => $month,
            'year' => $year
        );
        if($month < 1){
            $monthYearArray['month']+= 12;
            $monthYearArray['year']--;
        }elseif($month > 12){
            $monthYearArray['month'] -= 12;
            $monthYearArray['year']++;
        }
        if(strlen($monthYearArray['month'])< 2) $monthYearArray['month'] = '0'.$monthYearArray['month'];

        return $monthYearArray;
    }


    private function _setDefaults($options){
    	//@TODO Clever array_merge here
        if(!isset($options['month']) || empty($options['month'])){
            if(isset($this->params['named']['month'])){
                $options['month'] = $this->params['named']['month'];
            }else{
                $options['month'] = date('m');
            }

            if(strlen($options['month'])< 2) $options['month'] = '0'.$options['month'];

        }
        if(!isset($options['month']) || empty($options['year'])){
            if(isset($this->params['named']['year'])){
                $options['year'] = $this->params['named']['year'];
                if(strlen($options['year']  == 2) ) $options['year'] = '20'.$options['year'];
            }else{
                $options['year'] = date('Y');
            }

        }
        
        $options = array_merge(array('next_prev_count' => 2,'link_template' => array('month' => $options['month'],'year' => $options['year']),'show_day_link' => true),$options);
        

        return $options;
    }

    public function days_of_week(){
        $beginning = strtotime('Last Sunday');
        return array(
           strftime($this->day_abbreviation_format, $beginning),
           strftime($this->day_abbreviation_format, $beginning += 86400),
           strftime($this->day_abbreviation_format, $beginning += 86400),
           strftime($this->day_abbreviation_format, $beginning += 86400),
           strftime($this->day_abbreviation_format, $beginning += 86400),
           strftime($this->day_abbreviation_format, $beginning += 86400),
           strftime($this->day_abbreviation_format, $beginning += 86400),
        );
    }

}
?>