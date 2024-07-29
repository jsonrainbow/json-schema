<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

/**
 * Validates against the "format" property
 *
 * @author Justin Rainbow <justin.rainbow@gmail.com>
 * @link   http://tools.ietf.org/html/draft-zyp-json-schema-03#section-5.23
 */
class FormatConstraint extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function check($element, $schema = null, $path = null, $i = null)
    {
        if (!isset($schema->format)) {
            return;
        }

        switch ($schema->format) {
            case 'date':
                if (!$date = $this->validateDateTime($element, 'Y-m-d')) {
                    $this->addError($path, sprintf('Invalid date %s, expected format YYYY-MM-DD', json_encode($element)));
                }
                break;

            case 'time':
                if (!$this->validateDateTime($element, 'H:i:s')) {
                    $this->addError($path, sprintf('Invalid time %s, expected format hh:mm:ss', json_encode($element)));
                }
                break;
			case 'astime':
                if (!$this->validateDateTime($element, 'H:i')) {
                    $this->addError($path, sprintf('Invalid time %s, expected format hh:mm', json_encode($element)));
                }
                break;	

            case 'date-time':
                if (!$this->validateDateTime($element, 'Y-m-d\TH:i:s\Z') &&
                    !$this->validateDateTime($element, 'Y-m-d\TH:i:s.u\Z') &&
                    !$this->validateDateTime($element, 'Y-m-d\TH:i:sP') &&
                    !$this->validateDateTime($element, 'Y-m-d\TH:i:sO')
                ) {
                    $this->addError($path, sprintf('Invalid date-time %s, expected format YYYY-MM-DDThh:mm:ssZ or YYYY-MM-DDThh:mm:ss+hh:mm', json_encode($element)));
                }
                break;

            case 'utc-millisec':
                if (!$this->validateDateTime($element, 'U')) {
                    $this->addError($path, sprintf('Invalid time %s, expected integer of milliseconds since Epoch', json_encode($element)));
                }
                break;

            case 'regex':
                if (!$this->validateRegex($element)) {
                    $this->addError($path, 'Invalid regex format ' . $element);
                }
                break;

            case 'color':
                if (!$this->validateColor($element)) {
                    $this->addError($path, "Invalid color");
                }
                break;

            case 'style':
                if (!$this->validateStyle($element)) {
                    $this->addError($path, "Invalid style");
                }
                break;

            case 'phone':
                if (!$this->validatePhone($element)) {
                    $this->addError($path, "Invalid phone number");
                }
                break;
			case 'ttphone':
                $phoneStatus	=	$this->validateTTPhone($element);
				if(!$phoneStatus['status'])
				{
					$this->addError($path, $phoneStatus['message']);
				}
				break;	

            case 'uri':
                if (null === filter_var($element, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE)) {
                    $this->addError($path, "Invalid URL format");
                }
                break;

            case 'email':
				if(isset($element)&& $element!=='' && $element!==null){
					if (null === filter_var($element, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE)) {
						$this->addError($path, "Invalid email");
					}	
				}
                
                break;

            case 'ip-address':
            case 'ipv4':
                if (null === filter_var($element, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE | FILTER_FLAG_IPV4)) {
                    $this->addError($path, "Invalid IP address");
                }
                break;

            case 'ipv6':
                if (null === filter_var($element, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE | FILTER_FLAG_IPV6)) {
                    $this->addError($path, "Invalid IP address");
                }
                break;

            case 'host-name':
            case 'hostname':
                if (!$this->validateHostname($element)) {
                    $this->addError($path, "Invalid hostname");
                }
                break;
			case 'stoptime':
				if (!$this->validateStopTime($element,array('h:i A','h:i a','g:i A','g:i a'))) {
                    $this->addError($path, sprintf('Invalid stop time %s, expected format h:i A', json_encode($element)));
                }
				break;
			case 'stopdate':
				if (!$this->validateStopDate($element,array('m/d/Y','m/j/Y','n/d/Y','n/j/Y'))) {
                    $this->addError($path, sprintf('Invalid stop date %s, expected format m/d/Y', json_encode($element)));
                }
				break;
				
			case 'stopdatetime':
				if (!$this->validateStopDateTime($element,array('m/d/Y H:i:s T','m/d/Y H:i:s e','m/d/Y H:i:s O','m/d/Y H:i:s P'))) {
                    $this->addError($path, sprintf('Invalid stop date time %s, Expected format m/d/Y H:i:s O', json_encode($element)));
                }
				break;	

            default:
                // Do nothing so that custom formats can be used.
                break;
        }
    }

    protected function validateDateTime($datetime, $format)
    {
        if(trim($datetime)=='')
		{
			return true;
		}
		
		$dt = \DateTime::createFromFormat($format, $datetime);

        if (!$dt) {
            return false;
        }

        return $datetime === $dt->format($format);
    }

    protected function validateRegex($regex)
    {
        return false !== @preg_match('/' . $regex . '/', '');
    }

    protected function validateColor($color)
    {
        if (in_array(strtolower($color), array('aqua', 'black', 'blue', 'fuchsia',
            'gray', 'green', 'lime', 'maroon', 'navy', 'olive', 'orange', 'purple',
            'red', 'silver', 'teal', 'white', 'yellow'))) {
            return true;
        }

        return preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $color);
    }

    protected function validateStyle($style)
    {
        $properties     = explode(';', rtrim($style, ';'));
        $invalidEntries = preg_grep('/^\s*[-a-z]+\s*:\s*.+$/i', $properties, PREG_GREP_INVERT);

        return empty($invalidEntries);
    }

    protected function validatePhone($phone)
    {
        return preg_match('/^\+?(\(\d{3}\)|\d{3}) \d{3} \d{4}$/', $phone);
    }

    protected function validateHostname($host)
    {
        return preg_match('/^[_a-z]+\.([_a-z]+\.?)+$/i', $host);
    }
	
	protected function validateTTPhone($phone)
    {
		
        return array('status'=>true);
		
		/*if(trim($phone)=='')
		{
			return array('status'=>true);
		}
		
		$phone	=	\loadtrack\LoadTrackMultiStopServices::cleanDriverPhoneNumber($phone);
		$phoneValidStatus = \loadtrack\LoadTrackMultiStopServices::validatePhoneWithCelltrust($phone);
		if($phoneValidStatus['status']===true)
		{
			return array('status'=>true);
		}
		else
		{
			return array('status'=>false,'message'=>$phoneValidStatus['message']);
		}*/
    }
	
	protected function validateStopTime($datetime, $formatList)
    {
        $datetime	=	preg_replace("/[[:blank:]]+/"," ",$datetime);
		
		$validTime	=	false;
		
		for($i=0;$i<count($formatList);$i++)
		{
			
			$format	=	$formatList[$i];
			$dt = \DateTime::createFromFormat($format, $datetime);
	
			if (!$dt) {
				return false;
			}
			
			//echo $datetime.' === '.$dt->format($format);
			
			if($datetime === $dt->format($format))
			{
				$validTime	=	true;
				break;
			}
					
		
		}
		
		return $validTime;
        
    }
	protected function validateStopDateTime($datetime, $formatList)
    {
		$validDate	=	false;
		
		for($i=0;$i<count($formatList);$i++)
		{
			
			$format	=	$formatList[$i];
			$dt = \DateTime::createFromFormat($format, $datetime);
	
			if (!$dt) {
				return false;
			}
			
			//echo $datetime.' === '.$dt->format($format);
			
			if($datetime === $dt->format($format))
			{
				
				/*$currentDateTime	=	gmdate('m/d/Y H:i:s T');
				$currentTimeStamp	=	strtotime($currentDateTime);
				
				$stopDateTime		=	gmdate('m/d/Y H:i:s T',strtotime($datetime));
				$stopTimeStamp		=	strtotime($stopDateTime);
				
				//echo $stopTimeStamp."-".$currentTimeStamp;exit;
				
				if($stopTimeStamp-$currentTimeStamp>=0)
				{
					$validDate	=	true;
				}*/
				$validDate	=	true;
				break;
			}
					
		
		}
		
		return $validDate;
    }
}
