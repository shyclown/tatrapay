<?php
class TatraBanka
{
  // keys obtained from bank
	const KEY = ''; 
	const MID = '';
	
	
	const RSMS = ''; // phone number person receiving payment
	const REM = ''; // email of person receiving payment
	
	protected $strAMT; 
	protected $strCURR;
	protected $strVS;
	protected $strSS;
	protected $strCS;
	protected $strRURL;
	private $strSIGN;

        private $strIG;

	public function GetSign($str) 
	{ 
                $this->strIG = $str;

		$hash = substr (sha1($str,true), 0, 32 );
		
                $ael = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size ($ael), MCRYPT_RAND);
		
		$use_key = hex2bin(self::KEY);
		mcrypt_generic_init($ael, $use_key, $iv);
		
                $strSIGN = mcrypt_generic ($ael, $hash);
		
		mcrypt_generic_deinit ($ael);
		mcrypt_module_close($ael);
		
		$strSIGN = bin2hex($strSIGN);
		$strSIGN = substr (strtoupper($strSIGN), 0, 32 );
return $strSIGN;
	}                                                
 
	protected function SanitizeFloat($flt) 
	{
    	return str_replace(',', '.', sprintf('%.2f', $flt));
	} 
}


class TatraPay extends TatraBanka
{
	public function __construct($AMT = null, $CURR = null, $VS = null, $RURL = null, $SS = null, $CS = '0308') 
	{
                $this->strAMT = $this->SanitizeFloat($AMT);
	                        $this->strCURR = $CURR;
				$this->strVS = $VS;
				$this->strRURL = $RURL;
				$this->strSS = $SS;
				$this->strCS = $CS;
				$this->strSS = $SS;
				
				$strSIGN = $this->GetSign(self::MID . $this->strAMT . $CURR . $VS . $CS . $RURL);
                                $this->strSIGN = $strSIGN;
	} 
	

	public function GetUrl()  
	{
     //link to bank
			$url = sprintf('https://moja.tatrabanka.sk/cgi-bin/e-commerce/start/e-commerce.jsp?PT=TatraPay&MID=%s&AMT=%s&CURR=%s&VS=%s&CS=%s&RURL=%s&LANG=sk&AREDIR=1&SIGN=%s',
            self::MID,
			$this->strAMT,
			$this->strCURR,
			$this->strVS,$this->strCS,
			$this->strRURL,
			$this->strSIGN
			);
			if(self::RSMS != '')
						$url .= '&RSMS=' . self::RSMS;
			if(self::REM != '')
						$url .= '&REM=' . self::REM;
			
                       
			return $url;                                                                                                                                      
        }                                       

	public function VerifyReply() 
	{
                if(!isset($_GET['VS'])) 
                        return false;   
                if(!isset($_GET['RES']))
                        return false;   
                if(!isset($_GET['SIGN']))
                        return false;    
                $strToSign = $_GET['VS'] . $_GET['RES'];
                if($_GET['SIGN'] == $this->GetSign($strToSign)) 
				{
                        return true;                             
     			}                                                
     return false;                                    
	} 
}
