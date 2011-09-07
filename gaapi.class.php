<?
/*-----------------------------------------------------
Google Analytic Extract Data API Class
Created by: *nice* [http://u-blue.com]
Version: 1.0 
Since: 2010-10-02
-----------------------------------------------------*/
class gaApi{
	private $loginURL = 'https://www.google.com/accounts/ClientLogin';
  	private $reportURL = 'https://www.google.com/analytics/feeds/data';
  	private $accountURL = 'https://www.google.com/analytics/feeds/accounts/default';
  
    private $gaIds;
    private $gaAuth;
    
    public function __construct($username, $password){
			
			$this->authen($username,$password);
    }
    
    function setSiteID($siteid){
		$this->gaIds = $siteid;
    }
    
    private function authen($username,$password){
    	$params = array ('accountType' 	=> 'GOOGLE', 
                        'Email' 		=> $username,
                        'Passwd' 		=> $password,
                        'service' 		=> 'analytics',
                        'source'		=> 'ublueGaApi');
    	
    	$results=$this->httpRequest($this->loginURL,$params,"POST");

    	if (strpos($results, "\n") !== false && substr($results, 0, 5) != 'Error'){
            $results = explode("\n", $results);
            foreach ($results as $result){
                if (substr($result, 0, 4) == 'Auth'){
                   $this->gaAuth = trim(substr($result, 5));
                }
            }
        }else{
        	echo "Cannot connect to Google Analytic: $results";
        	exit();
        }
    }//end authen
    
	function listAccounts(){
		return ($this->parseAccount($this->httpRequest($this->accountURL,null)));
	}
	
	//Generate report $params=array('key'=>'ga:xxx','key'=>'ga:xxx')
	function genReport($params){
    	$results=$this->httpRequest($this->reportURL,$params,"PUT");
    	
    	if($results){
    		$arr=$this->parseXML($results);
    		return $arr;
    	}
	}

	//do request from google
	private function httpRequest($url,$params=null,$method="GET"){
		if(function_exists('curl_exec'))
		{
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$params['v'] = 2;
			if($params){
				//if post, params in post fields
				if($method=="POST"){
					curl_setopt($ch, CURLOPT_POST, 1); 
					curl_setopt($ch, CURLOPT_POSTFIELDS, $params); 
				}else{ 
					//else params in url
					$url = $url."?ids=".$this->gaIds."&".http_build_query($params);
				}
			}
            
			//if auth token recieved
			if($this->gaAuth!=null){
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: GoogleLogin auth=' . $this->gaAuth));
			}
			curl_setopt($ch, CURLOPT_URL, $url);
			$result=curl_exec($ch); 
			$info = curl_getinfo($ch);		
			curl_close($ch); 
			
			if($result==false || $info['http_code']!=200){
				echo "Fetch data error: $result";
				exit();
				return false;
			}else{
				return $result;
			}
		}else{
			//no curl
			echo "Error no cURL funcition";
			exit();
			return false;
		}
	}//end http request
	
	function parseAccount($xml){
		$xsource = new DOMDocument();
		$xsource->loadXML($xml);
		$xentries = $xsource->getElementsByTagName('entry');
		$i = 0;
		foreach($xentries as $xentry){
			$accounts[$i]['title'] = $xentry->getElementsByTagName('title')->item(0)->nodeValue;
			$accounts[$i]['tableId'] = $xentry->getElementsByTagName('tableId')->item(0)->nodeValue;
			$i++;
		}
		
		return $accounts;
	}
	
	//parse xml into array [i][[dimension,metric][value]]
	function parseXML($xml){
		//use dom to parse
		$xsource = new DOMDocument();
		$xsource->loadXML($xml);
        
		//get each entries
		$xentries = $xsource->getElementsByTagName('entry');
		$i=0;
        
		//process each entry
		foreach($xentries as $xentry){
        	//get each dimension into array
        	$dimensions=$xentry->getElementsByTagName('dimension');
        	foreach($dimensions as $dimension){
        		$tuple[$i][$dimension->getAttribute('name')]=$dimension->getAttribute('value');
        	}
        	//get each metric into array
        	$metrics=$xentry->getElementsByTagName('metric');
        	foreach($metrics as $metric){
        		$tuple[$i][$metric->getAttribute('name')]=$metric->getAttribute('value');
        	}
			$i++;
		}
		return $tuple;
	}// end parse xml
	
	//--------SET OF READY MADE FUNCTIONS-----------
	
	//Summery: visitors, unique visit, pageview, time on site, new visits, bounce rates
    function getSummery($from,$to){
    	$params = array ('metrics' 		=> 'ga:visits,ga:pageviews,ga:bounces,ga:timeOnSite,ga:newVisits,ga:entrances,ga:visitors',
                        'start-date' 	=> $from,
                        'end-date' 		=> $to);
        $result=$this->genReport($params);
        return $result[0];
    }
    
    //All time summery: visitors, page views
    function getAllTimeSummery(){
    	$now=date("Y-m-d");
    	$params = array ('metrics' 		=> 'ga:visits,ga:pageviews',
                        'start-date' 	=> '2005-01-01',
                        'end-date' 		=> $now);
        $result=$this->genReport($params);
        return $result[0];
    }
    
    //Last * days visitors
    function getVisits($from,$to,$limit){
    	$params = array ('metrics' 		=> 'ga:visits',
    					'dimensions'	=> 'ga:date',
                        'start-date' 	=> $from,
                        'end-date' 		=> $to,
                        'max-results' 	=> $limit,
                        'sort'			=> 'ga:date');
        return $this->genReport($params);
    }
    
    //Top search engine keywords
    function getTopKeyword($from,$to,$limit){
    	$params = array ('metrics' 		=> 'ga:visits',
    					'dimensions'	=> 'ga:keyword',
    					'filters'		=> 'ga:keyword!=(not set)',
                        'start-date' 	=> $from,
                        'end-date' 		=> $to,
                        'max-results' 	=> $limit,
                        'sort'			=> '-ga:visits');
        return $this->genReport($params);
    }
    
    //Top visitor countries
    function getTopCountry($from,$to,$limit){
    	$params = array ('metrics' 		=> 'ga:visits',
    					'dimensions'	=> 'ga:country',
                        'start-date' 	=> $from,
                        'end-date' 		=> $to,
                        'max-results' 	=> $limit,
                        'sort'			=> '-ga:visits');
        return $this->genReport($params);
    }
    
    //Top page views
	function getTopPage($from,$to,$limit){
    	$params = array ('metrics' 		=> 'ga:visits',
    					'dimensions'	=> 'ga:pagePath',
                        'start-date' 	=> $from,
                        'end-date' 		=> $to,
                        'max-results' 	=> $limit,
                        'sort'			=> '-ga:visits');
        return $this->genReport($params);
    }
    
    //Top referrer websites
    function getTopReferrer($from,$to,$limit){
    	$params = array ('metrics' 		=> 'ga:visits',
    					'dimensions'	=> 'ga:source',
                        'start-date' 	=> $from,
                        'end-date' 		=> $to,
                        'max-results' 	=> $limit,
                        'sort'			=> '-ga:visits');
        return $this->genReport($params);
    }
	
	//Top visitor browsers
    function getTopBrowser($from,$to,$limit){
    	$params = array ('metrics' 		=> 'ga:visits',
    					'dimensions'	=> 'ga:browser',
                        'start-date' 	=> $from,
                        'end-date' 		=> $to,
                        'max-results' 	=> $limit,
                        'sort'			=> '-ga:visits');
        return $this->genReport($params);
    }
    
    //Top visitor operating systems
	function getTopOs($from,$to,$limit){
    	$params = array ('metrics' 		=> 'ga:visits',
    					'dimensions'	=> 'ga:operatingSystem',
                        'start-date' 	=> $from,
                        'end-date' 		=> $to,
                        'max-results' 	=> $limit,
                        'sort'			=> '-ga:visits');
        return $this->genReport($params);
    }

    //----------------END READY MADE------------------
}
?>