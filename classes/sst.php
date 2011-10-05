<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
////////////////////////////////////////////////////////////////////////////////
//                                                                              
// Project   : Server to Server Transfer [SST] 2                                
// Filename  : downloader.php                                                   
// Purpose   : HTTP Downloader Implementation                                   
// Version   : 2.2
// Author    : James Azarja
// Author	: legolas558
//                                                                              

define('__SST_MAX_REDIRECT', 10);

        class classDownloader
        {
                var $url            = ""; // (string) Requested URL

                var $useragent      = ""; // (string) User Agent signature (act as ?)
                var $referer        = ""; // (string) Referer URL
                var $accept         = ""; // (string) Accept what ?
                var $userid         = ""; // (string) HTTP Authentication Userid
                var $d_password       = ""; // (string) HTTP Authentication Password

                var $proxyaddr      = ""; // (string) Use Proxy ?
                var $proxyport      = ""; // (string) Proxy port ?
                var $contenttype    = "";

                var $timeout        = 30;
                var $errorid        = 0;
                var $errormsg       = "";

                /* Respone */
                var $replyheader    = array();
                var $replybody      = "";
                var $replyproto     = "";
                var $replyversion   = "";
                var $replycode      = 0;
                var $replymsg       = "";
                var $redirected     = 0; // (int) count of redirections
                var $historyurl     = array();
                var $lasturl        = ""; // (string) Last request URL.
		
		// added by legolas558
		function classDownloader() {
			// Mozilla/5.0 (X11; U; Linux i686; it; rv:1.8.1.8) Gecko/20071023 Firefox/2.0.0.8
			$this->useragent 	= 'LaniusCMS/'.cms_version().' loves Gecko SST/2.2';
		}
                
                ####################################################################
                # Public Function

                function GetReplyHeaderValue($field)
                {
                        reset($this->replyheader);
                                while (list($key, $header) = each ($this->replyheader))
                                {
                                        if ($key==0) continue;
                                        if (!(strpos($header,$field)=== false))
                                        {
                                                        return trim(substr($header,
                                                                 strpos($header,":")+1,
                                                                 strlen($header)-strpos($header,":")+1));
                                        }
                                }
                                return "";
                }

                function Get($headers_only = false)
                {
                        do
                        {
                                
                                $this->lasturl = $this->url;
                                $redirection = "";

                                $parsedurl = parse_url($this->url);
                                $this->historyurl[]=$this->url;
                                $useproxy = (($this->proxyaddr != "") && ($this->proxyport != ""));
                                
                                if (!$useproxy)
                                {
                                        $host = $parsedurl["host"];
                                        if(isset($parsedurl["port"]))
										{
										$port = $parsedurl["port"];
										} else $port = "80";
										
                                        $hostname = $host;
                                } else
                                {
                                        $host = $this->proxyaddr;
                                        $port = $this->proxyport;
                                        $hostname = $parsedurl["host"];
                                }

                               // $port = $port ? $port : "80";

                                $sockethandle=@fsockopen($host,$port,$errid,$errmsg,30);
                                //status("Opening connection to $host");

                                if (!$sockethandle) {
                                       // status("Can't connect to $host");
                                        return false;
                                } else {
                                        // socket_set_timeout($sockhandle, $timeout);
                                        if (!isset($parsedurl["path"]))
						$parsedurl["path"]="/";
						
					if (!isset($parsedurl["query"]))
						$parsedurl['query'] = '';
					else $parsedurl['query'] = '?'.$parsedurl["query"];

                                        //status("Sending request");
                                        $request = "";
                                        if (!$useproxy)
                                        {
                                                $request.= "GET ".$parsedurl["path"].$parsedurl['query']." HTTP/1.0\r\n";
                                                $request.= "Host: $hostname\r\n";
                                        } else {
                                                $request.= "GET ".$this->url." HTTP/1.0\r\n";
                                                $request.= "Host: $hostname\r\n";
                                        }
                                        if ($this->referer!="")
                                                { $request.= "Referer: $this->referer\r\n"; }
                                        if ($this->useragent!="")
                                                { $request.= "User-Agent: $this->useragent\r\n";}
                                        if ($this->accept!="")
                                                { $request.= "Accept: $this->accept\r\n";}
                                        if (($this->password!=="") || ($this->userid!==""))
                                        {
	                                        $request.= "Authorization: Basic ".base64_encode($this->userid.":".$this->password)."\r\n";
                                        }
                                        $request.= "\r\n";

                                        /* Send The Request */
                                        fwrite($sockethandle, $request);

                                        $result = "";
					//L: who estabilished this limit?
                                        $maxsize=1048576*5;
                                        //status("Waiting for reply");
					$fck = true;
                                        while (!feof($sockethandle))
                                        {
                                                $size = strlen($result);
                                                if ($size>$maxsize)
                                                {
                                                        //status("Content size is too big (maximum: $maxsize)");
                                                        return false;
                                                }
                                                //status("Downloading from server [".strlen($result)." bytes]");
                                                $result.= fread($sockethandle,4096);
						if ($headers_only) {
							$contentpos = strpos ($result,"\r\n\r\n");
							if ($contentpos !== false)
								break;
						}
						//L: check if first chunk is empty
						if ($fck) {
							if (!strlen($result))
								return false;
							$fck = false;
						}
//                                                echo " ";
                                        }

                                        fclose ($sockethandle);
					if (!$headers_only) {
						$contentpos = strpos ($result,"\r\n\r\n");
						$this->replybody       = substr($result,$contentpos+4,strlen($result)-($contentpos+4));
					}
					
                                        $this->replyheader     = split("\r\n",substr($result,0,$contentpos+2));

                                        /* Parsing Header */
                                        if (ereg("([A-Z]{4})/([0-9.]{3}) ([0-9]{3})",$this->replyheader[0],$regs)) {
                                                $this->replyproto      = $regs[1];
                                                $this->replyversion    = $regs[2];
                                                $this->replycode       = $regs[3];
                                                $this->replymsg        = substr($this->replyheader[0],
                                                strpos($this->replyheader[0],$this->replycode)+strlen($this->replycode)+1,
                                                strlen($this->replyheader[0])-strpos($this->replyheader[0],$this->replycode)+1);
                                        }
					
					// added by legolas558 - parse spurious Status: header
					$_status = $this->getreplyheadervalue('Status');
					if ($_status) {
						$_status = explode(' ', $_status);
						$this->replycode = (int)current($_status);
					}

                                        if ($redirection = $this->getreplyheadervalue("Location")) {
						// prevent infinite redirection
                                                if ($this->redirected++ == __SST_MAX_REDIRECT)
							return false;
						//L: fixed to allow HTTPS redirection
                                                if (!is_url($redirection)) {
                                                        $this->url = dirname($this->lasturl)."/".$redirection; 
                                                } else {
                                                        $this->url = $redirection;
                                                }
                                        }
                                        
                                        if (!$redirection) return true;
                                }
                                
                        } while (1); 
                }
		
		// added by legolas558
		function DownloadHeaders($strURL, $strUsername = '', $strPassword = '') {
			$this->url			= $strURL;
			$this->userid 	 	= $strUsername;
			$this->password 	= $strPassword;
                   
			return $this->Get(true);
		}
		
		function AttachmentFilename() {
			if ($fnd = $this->GetReplyHeaderValue('Content-Disposition')) {
				if (($fnd[0] == '"') && ($fnd[strlen($fnd)-1]=='"'))
					$fnd = substr($fnd, 1, -1);
				// security please
				return basename($fnd);
			}
			return '';
		}
        
                function Download($strURL, $strFilename=null, $status=true, $strUsername="", $strPassword="")
                {
					$this->url			= $strURL;
					$this->userid 	 	= $strUsername;
					$this->password 	= $strPassword;
                    
					$this->Get();
					if ($this->replycode!=200 && $this->replycode!=302)
						return false;

					if (isset($strFilename)) {
						global $d_root;
						require_once $d_root.'admin/classes/fs.php';
						$fs = new FS();
						return $fs->put_contents($strFilename,$this->replybody);
					} else
						return $this->replybody;
                        
                }
                
        }
?>