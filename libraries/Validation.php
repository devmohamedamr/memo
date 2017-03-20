<?php

/**
Hispanicode Validation
Copyright (C) 2016  http://hispanicode.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Form validation model with fantastic tools for the client and server side with php and jQuery.
 *
 * @author manudavgonz@gmail.com
 */
 
namespace Hispanic;

class Validation
{
    protected $error_messages = array();
    protected $msg = null;
    protected $attributes = array();
    protected $clientValidation = array();
    protected $clientValidationEvents = array();
    protected $lang = "en";
    protected $validation_translate = array();
    
    public function __construct()
    {
        require "translate/translate.php";
        $this->validation_translate = $validation_translate;
    }
    
    public function server($rules, $messages = array())
    {
        if (!isset($_REQUEST)) {
            return;
        }
        foreach ($rules as $key => $val) {
            $value = null;
            if (!isset($_FILES[$key])) {
                if (isset($_REQUEST[$key])) {
                    $value = $_REQUEST[$key];
                }
            }
            if (!array_key_exists($key, $this->getAttributes())) {
                $this->attributes[$key] = ucfirst($key);
            }
            $conditions = $rules[$key];
            $conditions = explode("|", $conditions);

            foreach ($conditions as $condition) {
                $pos = strpos($condition, ":");
                if ($pos !== false) {
                    $arg_condition = substr($condition, 0, $pos);
                    $val_condition = substr($condition, $pos+1, strlen($condition)-1);
                } else {
                    $arg_condition = $condition;
                    $val_condition = null;
                }
                
                $search = array(
                    ":attribute",
                    ":value",
                    ":condition"
                );
                
                $replace = array(
                    $this->attributes[$key],
                    $value,
                    $val_condition
                );
                switch ($arg_condition) {
                    case "required":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["required"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (empty($value)) {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;
                        
                    case "checked":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["checked"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if ($value == null) {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;
                    
                    case "min_length":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["min_length"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (strlen($value) < $val_condition && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;

                    case "max_length":
                        
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["max_length"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (strlen($value) > $val_condition && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;

                    case "min":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["min"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if ($value < $val_condition && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;

                    case "max":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["max"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if ($value > $val_condition && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;

                    case "between":
                        $v = explode("-", $val_condition);
                        $min = $v[0];
                        $max = $v[1];
                        array_push($search, ":min");
                        array_push($replace, $min);
                        array_push($search, ":max");
                        array_push($replace, $max);
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["between"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (strlen($value) < $min || strlen($value) > $max && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;
                        
                    case "range":
                        $v = explode("-", $val_condition);
                        $min = $v[0];
                        $max = $v[1];
                        array_push($search, ":min");
                        array_push($replace, $min);
                        array_push($search, ":max");
                        array_push($replace, $max);
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["range"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if ($value < $min || $value > $max && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;

                    case "name":
                        $chars = "a-záéíóúàèìòùäëïöüâêîôûñ\s";
                        array_push($search, ":chars");
                        array_push($replace, $chars);
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["name"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (!preg_match("/^[a-záéíóúàèìòùäëïöüâêîôûñ\s]+$/i", $value) && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;
                        
                    case "alpha":
                        $chars = "a-záéíóúàèìòùäëïöüâêîôûñ";
                        array_push($search, ":chars");
                        array_push($replace, $chars);
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["alpha"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (!preg_match("/^[a-záéíóúàèìòùäëïöüâêîôûñ]+$/i", $value) && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;

                    case "alphanumeric":
                        $chars = "0-9a-záéíóúàèìòùäëïöüâêîôûñ";
                        array_push($search, ":chars");
                        array_push($replace, $chars);
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["alphanumeric"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (!preg_match("/^[0-9a-záéíóúàèìòùäëïöüâêîôûñ]+$/i", $value) && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;

                    case "digit":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["digit"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (!preg_match("/^[0-9]+$/", $value) && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;

                    case "email":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["email"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (!preg_match("/^(([^<>()\[\]\\.,;:\s@\"]+(\.[^<>()\[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/", $value) && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;
                        
                    case "ip":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["ip"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (!preg_match("/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/", $value) && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;
                        
                    case "url":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["url"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (!filter_var($value, FILTER_VALIDATE_URL) !== false && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;
                        
                    case "date":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["date"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        $datetime = new \DateTime();
                        if ($datetime->createFromFormat($val_condition, $value) === false && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;
                        
                    case "time":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["time"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        $datetime = new \DateTime();
                        if ($datetime->createFromFormat($val_condition, $value) === false && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;
                        
                    case "datetime":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["datetime"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        $datetime = new \DateTime();
                        if ($datetime->createFromFormat($val_condition, $value) === false && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;

                    case "regex":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["regex"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (!preg_match($val_condition, $value) && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;

                    case "equalsTo":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["equalsTo"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (isset($_REQUEST[$val_condition])) {
                            if ($value != $_REQUEST[$val_condition]) {
                                if (!$this->errorInCollection($key)) {
                                    $field = array($key => $this->msg);
                                    $this->error_messages[$key] = $this->msg;
                                }
                            }
                        }
                        break;

                    case "float":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["float"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (!filter_var($value, FILTER_VALIDATE_FLOAT) !== false && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;
                        
                    case "integer":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["integer"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (!filter_var($value, FILTER_VALIDATE_INT) !== false && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;
                        
                    case "numeric":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["numeric"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (!is_numeric($value) && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;
                        
                    case "contains":
                        $match = explode(",", $val_condition);
                        array_push($search, ":contains");
                        array_push($replace, implode(" - ", $match));
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["contains"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        $isValid = false;
                        foreach ($match as $m) {
                            if ($m == $value) {
                                $isValid = true;
                            }
                        }
                        if (!$isValid && $value != "") {
                            if (!$this->errorInCollection($key)) {
                                $field = array($key => $this->msg);
                                $this->error_messages[$key] = $this->msg;
                            }
                        }
                        break;

                    case "file_required":
                            if (isset($_FILES[$key]["size"][0])) {
                                if (array_key_exists($key.".".$arg_condition, $messages)) {
                                    $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                                } else {
                                    $string = $this->validation_translate[$this->lang]["file_required"];
                                    $this->msg = str_replace($search, $replace, $string);
                                }
                                if ($_FILES[$key]["size"][0] == 0) {
                                    if (!$this->errorInCollection($key)) {
                                        $field = array($key => $this->msg);
                                        $this->error_messages[$key] = $this->msg;
                                    }
                                }
                            } else if (isset($_FILES[$key]["size"])) {
								if (array_key_exists($key.".".$arg_condition, $messages)) {
									$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
								} else {
									$string = $this->validation_translate[$this->lang]["file_required"];
									$this->msg = str_replace($search, $replace, $string);
								}
								if ($_FILES[$key]["size"] == 0) {
									if (!$this->errorInCollection($key)) {
										$field = array($key => $this->msg);
										$this->error_messages[$key] = $this->msg;
									}
								}
                        }
                        break;
						
                    case "min_files":
						if (isset($_FILES[$key]["size"][0])) {
							if (array_key_exists($key.".".$arg_condition, $messages)) {
								$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
							} else {
								$string = $this->validation_translate[$this->lang]["min_files"];
								$this->msg = str_replace($search, $replace, $string);
							}
							if (isset($_FILES[$key]) && count($_FILES[$key]["name"]) < $val_condition) {
								if (!$this->errorInCollection($key)) {
									$field = array($key => $this->msg);
									$this->error_messages[$key] = $this->msg;
								}
							}
						}
                        break;

                    case "max_files":
						if (isset($_FILES[$key]["size"][0])) {
							if (array_key_exists($key.".".$arg_condition, $messages)) {
								$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
							} else {
								$string = $this->validation_translate[$this->lang]["max_files"];
								$this->msg = str_replace($search, $replace, $string);
							}
							if (isset($_FILES[$key]) && count($_FILES[$key]["name"]) > $val_condition) {
								if (!$this->errorInCollection($key)) {
									$field = array($key => $this->msg);
									$this->error_messages[$key] = $this->msg;
								}
							}
						}
                        break;

                    case "file_min_size":
						if (isset($_FILES[$key]["size"][0])) {
							foreach (array_keys($_FILES[$key]["name"]) as $index) {
								$file = $_FILES[$key]["name"][$index];
								array_push($search, ":file");
								array_push($replace, $file);
								$megabytes = $val_condition/(1024*1024);
								array_push($search, ":megabytes");
								array_push($replace, $megabytes);
								if (array_key_exists($key.".".$arg_condition, $messages)) {
									$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
								} else {
									$string = $this->validation_translate[$this->lang]["file_min_size"];
									$this->msg = str_replace($search, $replace, $string);
								}
								if ($_FILES[$key]["size"][$index] < $val_condition) {
									if (!$this->errorInCollection($key)) {
										$field = array($key => $this->msg);
										$this->error_messages[$key] = $this->msg;
									}
								}
							}
						}
						else if (isset($_FILES[$key]["size"])) {
							if ($_FILES[$key]["size"] > 0) {
								$file = $_FILES[$key]["name"];
								array_push($search, ":file");
								array_push($replace, $file);
								$megabytes = $val_condition/(1024*1024);
								array_push($search, ":megabytes");
								array_push($replace, $megabytes);
								if (array_key_exists($key.".".$arg_condition, $messages)) {
									$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
								} else {
									$string = $this->validation_translate[$this->lang]["file_min_size"];
									$this->msg = str_replace($search, $replace, $string);
								}
								if ($_FILES[$key]["size"] < $val_condition) {
									if (!$this->errorInCollection($key)) {
										$field = array($key => $this->msg);
										$this->error_messages[$key] = $this->msg;
									}
								}
							}
						}
                        break;

                    case "file_max_size":
						if (isset($_FILES[$key]["size"][0])) {
							foreach (array_keys($_FILES[$key]["name"]) as $index) {
								$file = $_FILES[$key]["name"][$index];
								array_push($search, ":file");
								array_push($replace, $file);
								$megabytes = $val_condition/(1024*1024);
								array_push($search, ":megabytes");
								array_push($replace, $megabytes);
								if (array_key_exists($key.".".$arg_condition, $messages)) {
									$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
								} else {
									$string = $this->validation_translate[$this->lang]["file_max_size"];
									$this->msg = str_replace($search, $replace, $string);
								}
								if ($_FILES[$key]["size"][$index] > $val_condition) {
									if (!$this->errorInCollection($key)) {
										$field = array($key => $this->msg);
										$this->error_messages[$key] = $this->msg;
									}
								}
							}
						}
						else if (isset($_FILES[$key]["size"])) {
							if ($_FILES[$key]["size"] > 0) {
								$file = $_FILES[$key]["name"];
								array_push($search, ":file");
								array_push($replace, $file);
								$megabytes = $val_condition/(1024*1024);
								array_push($search, ":megabytes");
								array_push($replace, $megabytes);
								if (array_key_exists($key.".".$arg_condition, $messages)) {
									$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
								} else {
									$string = $this->validation_translate[$this->lang]["file_max_size"];
									$this->msg = str_replace($search, $replace, $string);
								}
								if ($_FILES[$key]["size"] > $val_condition) {
									if (!$this->errorInCollection($key)) {
										$field = array($key => $this->msg);
										$this->error_messages[$key] = $this->msg;
									}
								}
							}
						}
                        break;
                        
                    case "mime":
                            if (isset($_FILES[$key]["size"][0])) {
								if (is_array($_FILES[$key]["name"])) {
									foreach (array_keys($_FILES[$key]["name"]) as $index) {
										if ($_FILES[$key]["size"][$index] > 0) {
											$types = explode(",", $val_condition);
											$type = $_FILES[$key]["type"][$index];
											$type = explode("/", $type);
											$ext = $type[1];
											$is_allowed = false;
											$file = $_FILES[$key]["name"][$index];
											array_push($search, ":file");
											array_push($replace, $file);
											array_push($search, ":mime");
											array_push($replace, implode(" - ", $types));
											if (array_key_exists($key.".".$arg_condition, $messages)) {
												$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
											} else {
												$string = $this->validation_translate[$this->lang]["mime"];
												$this->msg = str_replace($search, $replace, $string);
											}
											foreach ($types as $e) {
												if (strtolower($e) == strtolower($ext)) {
													$is_allowed = true;
													break;
												}
											}
											if (!$is_allowed) {
												if (!$this->errorInCollection($key)) {
													$field = array($key => $this->msg);
													$this->error_messages[$key] = $this->msg;
												}
											}
										}
									}
								}
                            } else if (isset($_FILES[$key]["size"])) {
								if ($_FILES[$key]["size"] > 0) {
									$types = explode(",", $val_condition);
									$type = $_FILES[$key]["type"];
									$type = explode("/", $type);
									$ext = $type[1];
									$is_allowed = false;
									$file = $_FILES[$key]["name"];
									array_push($search, ":file");
									array_push($replace, $file);
									array_push($search, ":mime");
									array_push($replace, implode(" - ", $types));
									if (array_key_exists($key.".".$arg_condition, $messages)) {
										$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
									} else {
										$string = $this->validation_translate[$this->lang]["mime"];
										$this->msg = str_replace($search, $replace, $string);
									}
									foreach ($types as $e) {
										if (strtolower($e) == strtolower($ext)) {
											$is_allowed = true;
											break;
										}
									}
									if (!$is_allowed) {
										if (!$this->errorInCollection($key)) {
											$field = array($key => $this->msg);
											$this->error_messages[$key] = $this->msg;
										}
									}
							}
                        }
                        break;
                        
                    case "img_min_width":
						if (isset($_FILES[$key]["size"][0])) {
							foreach (array_keys($_FILES[$key]["name"]) as $index) {
								if ($_FILES[$key]["size"][$index] > 0) {
									$content_type = explode("/", $_FILES[$key]["type"][$index]);
									$image = $content_type[0];
									if (strtolower($image) == "image") {
										$size = getimagesize($_FILES[$key]["tmp_name"][$index]);
										$file = $_FILES[$key]["name"][$index];
										array_push($search, ":file");
										array_push($replace, $file);
										if (array_key_exists($key.".".$arg_condition, $messages)) {
											$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
										} else {
											$string = $this->validation_translate[$this->lang]["img_min_width"];
											$this->msg = str_replace($search, $replace, $string);
										}
										if ($size[0] < $val_condition) {
											if (!$this->errorInCollection($key)) {
												$field = array($key => $this->msg);
												$this->error_messages[$key] = $this->msg;
											}
										}
									}
								}
							}
						} else if (isset($_FILES[$key]["size"])) {
							if ($_FILES[$key]["size"] > 0) {
								$content_type = explode("/", $_FILES[$key]["type"]);
								$image = $content_type[0];
								if (strtolower($image) == "image") {
									$size = getimagesize($_FILES[$key]["tmp_name"]);
									$file = $_FILES[$key]["name"];
									array_push($search, ":file");
									array_push($replace, $file);
									if (array_key_exists($key.".".$arg_condition, $messages)) {
										$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
									} else {
										$string = $this->validation_translate[$this->lang]["img_min_width"];
										$this->msg = str_replace($search, $replace, $string);
									}
									if ($size[0] < $val_condition) {
										if (!$this->errorInCollection($key)) {
											$field = array($key => $this->msg);
											$this->error_messages[$key] = $this->msg;
										}
									}
								}
							}
                        }
                        break;
                        
                    case "img_max_width":
						if (isset($_FILES[$key]["size"][0])) {
							foreach (array_keys($_FILES[$key]["name"]) as $index) {
								if ($_FILES[$key]["size"][$index] > 0) {
									$content_type = explode("/", $_FILES[$key]["type"][$index]);
									$image = $content_type[0];
									if (strtolower($image) == "image") {
										$size = getimagesize($_FILES[$key]["tmp_name"][$index]);
										$file = $_FILES[$key]["name"][$index];
										array_push($search, ":file");
										array_push($replace, $file);
										if (array_key_exists($key.".".$arg_condition, $messages)) {
											$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
										} else {
											$string = $this->validation_translate[$this->lang]["img_max_width"];
											$this->msg = str_replace($search, $replace, $string);
										}
										if ($size[0] > $val_condition) {
											if (!$this->errorInCollection($key)) {
												$field = array($key => $this->msg);
												$this->error_messages[$key] = $this->msg;
											}
										}
									}
								}
							}
						} else if (isset($_FILES[$key]["size"])) {
							if ($_FILES[$key]["size"] > 0) {
								$content_type = explode("/", $_FILES[$key]["type"]);
								$image = $content_type[0];
								if (strtolower($image) == "image") {
									$size = getimagesize($_FILES[$key]["tmp_name"]);
									$file = $_FILES[$key]["name"];
									array_push($search, ":file");
									array_push($replace, $file);
									if (array_key_exists($key.".".$arg_condition, $messages)) {
										$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
									} else {
										$string = $this->validation_translate[$this->lang]["img_max_width"];
										$this->msg = str_replace($search, $replace, $string);
									}
									if ($size[0] > $val_condition) {
										if (!$this->errorInCollection($key)) {
											$field = array($key => $this->msg);
											$this->error_messages[$key] = $this->msg;
										}
									}
								}
							}
                        }
                        break;
                        
                    case "img_min_height":
                            if (isset($_FILES[$key]["size"][0])) {
                                foreach (array_keys($_FILES[$key]["name"]) as $index) {
                                    if ($_FILES[$key]["size"][$index] > 0) {
                                        $content_type = explode("/", $_FILES[$key]["type"][$index]);
                                        $image = $content_type[0];
                                        if (strtolower($image) == "image") {
                                            $size = getimagesize($_FILES[$key]["tmp_name"][$index]);
                                            $file = $_FILES[$key]["name"][$index];
                                            array_push($search, ":file");
                                            array_push($replace, $file);
                                            if (array_key_exists($key.".".$arg_condition, $messages)) {
                                                $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                                            } else {
                                                $string = $this->validation_translate[$this->lang]["img_min_height"];
                                                $this->msg = str_replace($search, $replace, $string);
                                            }
                                            if ($size[1] < $val_condition) {
                                                if (!$this->errorInCollection($key)) {
                                                    $field = array($key => $this->msg);
                                                    $this->error_messages[$key] = $this->msg;
                                                }
                                            }
                                        }
                                    }
                                }
                            } else if (isset($_FILES[$key]["size"])) {
								if ($_FILES[$key]["size"] > 0) {
									$content_type = explode("/", $_FILES[$key]["type"]);
									$image = $content_type[0];
									if (strtolower($image) == "image") {
										$size = getimagesize($_FILES[$key]["tmp_name"]);
										$file = $_FILES[$key]["name"];
										array_push($search, ":file");
										array_push($replace, $file);
										if (array_key_exists($key.".".$arg_condition, $messages)) {
											$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
										} else {
											$string = $this->validation_translate[$this->lang]["img_min_height"];
											$this->msg = str_replace($search, $replace, $string);
										}
										if ($size[1] < $val_condition) {
											if (!$this->errorInCollection($key)) {
												$field = array($key => $this->msg);
												$this->error_messages[$key] = $this->msg;
											}
										}
									}
							}
                        }
                        break;
                        
                    case "img_max_height":
                            if (isset($_FILES[$key]["size"][0])) {
                                foreach (array_keys($_FILES[$key]["name"]) as $index) {
                                    if ($_FILES[$key]["size"][$index] > 0) {
                                        $content_type = explode("/", $_FILES[$key]["type"][$index]);
                                        $image = $content_type[0];
                                        if (strtolower($image) == "image") {
                                            $size = getimagesize($_FILES[$key]["tmp_name"][$index]);
                                            $file = $_FILES[$key]["name"][$index];
                                            array_push($search, ":file");
                                            array_push($replace, $file);
                                            if (array_key_exists($key.".".$arg_condition, $messages)) {
                                                $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                                            } else {
                                                $string = $this->validation_translate[$this->lang]["img_max_height"];
                                                $this->msg = str_replace($search, $replace, $string);
                                            }
                                            if ($size[1] > $val_condition) {
                                                if (!$this->errorInCollection($key)) {
                                                    $field = array($key => $this->msg);
                                                    $this->error_messages[$key] = $this->msg;
                                                }
                                            }
                                        }
                                    }
                                }
                            } else if (isset($_FILES[$key]["size"])) {
								if ($_FILES[$key]["size"] > 0) {
									$content_type = explode("/", $_FILES[$key]["type"]);
									$image = $content_type[0];
									if (strtolower($image) == "image") {
										$size = getimagesize($_FILES[$key]["tmp_name"]);
										$file = $_FILES[$key]["name"];
										array_push($search, ":file");
										array_push($replace, $file);
										if (array_key_exists($key.".".$arg_condition, $messages)) {
											$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
										} else {
											 $string = $this->validation_translate[$this->lang]["img_max_height"];
											$this->msg = str_replace($search, $replace, $string);
										}
										if ($size[1] > $val_condition) {
											if (!$this->errorInCollection($key)) {
												$field = array($key => $this->msg);
												$this->error_messages[$key] = $this->msg;
											}
										}
									}
								}
							}
                        break;
                    }
            }
        }
    }
    
    public function client($rules, $messages = array(), $events = array())
    {
        $iterator = 0;
        $e = 0;
        foreach ($rules as $key => $val) {
			
            $conditions = $rules[$key];
            $conditions = explode("|", $conditions);
			
            if (!array_key_exists($key, $this->getAttributes())) {
                $this->attributes[$key] = ucfirst($key);
            }

            foreach ($conditions as $condition) {
                $pos = strpos($condition, ":");
                if ($pos !== false) {
                    $arg_condition = substr($condition, 0, $pos);
                    $val_condition = substr($condition, $pos+1, strlen($condition)-1);
                } else {
                    $arg_condition = $condition;
                    $val_condition = null;
                }
                
                
                $search = array(
                    ":attribute",
                    ":condition"
                );
                $replace = array(
                    $this->attributes[$key],
                    $val_condition
                );

                switch ($arg_condition) {
                    
                    case "required":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["required"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_required('".$key."', '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_required('".$key."', '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "checked":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["checked"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_checked('".$key."', '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_checked('".$key."', '".htmlspecialchars($this->msg)."')");
                        break;
                    
                    case "min_length":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["min_length"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_min_length('".$key."', ".$val_condition.", '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_min_length('".$key."', ".$val_condition.", '".htmlspecialchars($this->msg)."')");
                        break;

                    case "max_length":
                        
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["max_length"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_max_length('".$key."', ".$val_condition.", '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_max_length('".$key."', ".$val_condition.", '".htmlspecialchars($this->msg)."')");
                        break;

                    case "min":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["min"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_min('".$key."', ".$val_condition.", '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_min('".$key."', ".$val_condition.", '".htmlspecialchars($this->msg)."')");
                        break;

                    case "max":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["max"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_max('".$key."', ".$val_condition.", '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_max('".$key."', ".$val_condition.", '".htmlspecialchars($this->msg)."')");
                        break;

                    case "between":
                        $v = explode("-", $val_condition);
                        $min = $v[0];
                        $max = $v[1];
                        array_push($search, ":min");
                        array_push($replace, $min);
                        array_push($search, ":max");
                        array_push($replace, $max);
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["between"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_between('".$key."', ".$min.", ".$max.", '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_between('".$key."', ".$min.", ".$max.", '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "range":
                        $v = explode("-", $val_condition);
                        $min = $v[0];
                        $max = $v[1];
                        array_push($search, ":min");
                        array_push($replace, $min);
                        array_push($search, ":max");
                        array_push($replace, $max);
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["range"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_range('".$key."', ".$min.", ".$max.", '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_range('".$key."', ".$min.", ".$max.", '".htmlspecialchars($this->msg)."')");
                        break;

                    case "name":
                        $chars = "a-záéíóúàèìòùäëïöüâêîôûñ\s";
                        array_push($search, ":chars");
                        array_push($replace, $chars);
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["name"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_regex('".$key."', /^[a-záéíóúàèìòùäëïöüâêîôûñ\s]+$/i, '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_regex('".$key."', /^[a-záéíóúàèìòùäëïöüâêîôûñ\s]+$/i, '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "alpha":
                        $chars = "a-záéíóúàèìòùäëïöüâêîôûñ";
                        array_push($search, ":chars");
                        array_push($replace, $chars);
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["alpha"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_regex('".$key."', /^[a-záéíóúàèìòùäëïöüâêîôûñ]+$/i, '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_regex('".$key."', /^[a-záéíóúàèìòùäëïöüâêîôûñ]+$/i, '".htmlspecialchars($this->msg)."')");
                        break;

                    case "alphanumeric":
                        $chars = "0-9a-záéíóúàèìòùäëïöüâêîôûñ";
                        array_push($search, ":chars");
                        array_push($replace, $chars);
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["alphanumeric"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_regex('".$key."', /^[0-9a-záéíóúàèìòùäëïöüâêîôûñ]+$/i, '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_regex('".$key."', /^[0-9a-záéíóúàèìòùäëïöüâêîôûñ]+$/i, '".htmlspecialchars($this->msg)."')");
                        break;

                    case "digit":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["digit"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_regex('".$key."', /^[0-9]+$/, '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_regex('".$key."', /^[0-9]+$/, '".htmlspecialchars($this->msg)."')");
                        break;

                    case "email":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["email"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_regex('".$key."', /^(([^<>()\[\]\\.,;:\s@\"]+(\.[^<>()\[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/, '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_regex('".$key."', /^(([^<>()\[\]\\.,;:\s@\"]+(\.[^<>()\[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/, '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "ip":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["ip"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_regex('".$key."', /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/, '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_regex('".$key."', /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/, '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "url":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["url"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_regex('".$key."', /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i, '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_regex('".$key."', /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i, '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "date":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["date"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_date('".$key."', '".$val_condition."', '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_date('".$key."', '".$val_condition."', '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "time":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["time"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_time('".$key."', '".$val_condition."', '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_time('".$key."', '".$val_condition."', '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "datetime":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["datetime"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_datetime('".$key."', '".$val_condition."', '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_datetime('".$key."', '".$val_condition."', '".htmlspecialchars($this->msg)."')");
                        break;

                    case "regex":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["regex"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_regex('".$key."', $val_condition, '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_regex('".$key."', $val_condition, '".htmlspecialchars($this->msg)."')");
                        break;

                    case "equalsTo":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["equalsTo"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_equalsTo('".$key."', '".$val_condition."', '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_equalsTo('".$key."', '".$val_condition."', '".htmlspecialchars($this->msg)."')");
                        break;

                    case "float":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["float"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_float('".$key."', '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_float('".$key."', '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "integer":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["integer"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_integer('".$key."', '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_integer('".$key."', '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "numeric":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["numeric"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_numeric('".$key."', '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_numeric('".$key."', '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "contains":
                        $match = explode(",", $val_condition);
                        array_push($search, ":contains");
                        array_push($replace, implode(" - ", $match));
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["contains"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_contains('".$key."', '".$val_condition."', '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_contains('".$key."', '".$val_condition."', '".htmlspecialchars($this->msg)."')");
                        break;

                    case "file_required":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["file_required"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_file_required('".$key."', '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_file_required('".$key."', '".htmlspecialchars($this->msg)."')");
                        break;
						
                    case "min_files":
						if (array_key_exists($key.".".$arg_condition, $messages)) {
							$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
						} else {
							$string = $this->validation_translate[$this->lang]["min_files"];
							$this->msg = str_replace($search, $replace, $string);
						}
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_min_files('".$key."', ".$val_condition.", '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
						array_push($this->clientValidation, "validation_min_files('".$key."', ".$val_condition.", '".htmlspecialchars($this->msg)."')");
                        break;

                    case "max_files":
						if (array_key_exists($key.".".$arg_condition, $messages)) {
							$this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
						} else {
							$string = $this->validation_translate[$this->lang]["max_files"];
							$this->msg = str_replace($search, $replace, $string);
						}
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_max_files('".$key."', ".$val_condition.", '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
						array_push($this->clientValidation, "validation_max_files('".$key."', ".$val_condition.", '".htmlspecialchars($this->msg)."')");
                        break;
						
                    case "file_min_size":
                        $megabytes = $val_condition/(1024*1024);
                        array_push($search, ":megabytes");
                        array_push($replace, $megabytes);
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["file_min_size"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_file_min_size('".$key."', $val_condition, '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_file_min_size('".$key."', $val_condition, '".htmlspecialchars($this->msg)."')");
                        break;

                    case "file_max_size":
                        $megabytes = $val_condition/(1024*1024);
                        array_push($search, ":megabytes");
                        array_push($replace, $megabytes);
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["file_max_size"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_file_max_size('".$key."', $val_condition, '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_file_max_size('".$key."', $val_condition, '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "mime":
                        $mimes = explode(",", $val_condition);
                        array_push($search, ":mime");
                        array_push($replace, implode(" - ", $mimes));
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["mime"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        if (array_key_exists($key, $events)) {
                            $the_events = $events[$key];
                            $the_events = explode("|", $the_events);
                            
                            foreach ($the_events as $event) {
                                $this->clientValidationEvents[$iterator][$key][$event][$e] = "validation_mime('".$key."', '".$val_condition."', '".htmlspecialchars($this->msg)."')";
                                $e++;
                            }
                        }
                        array_push($this->clientValidation, "validation_mime('".$key."', '".$val_condition."', '".htmlspecialchars($this->msg)."')");
                        break;
                        
                    case "img_min_width":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["img_min_width"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        $js =  "window.URL = window.URL || window.webkitURL;
                                elBrowse = document.getElementById('".$key."');
                                useBlob = false && window.URL;
                                elBrowse.addEventListener('change', function() {
                                var files  = this.files;
                                var errors = false;
                                error_box = $('#error_".$key."');
                                error_box.html('');
                                if (!files) {
                                    errors = true;
                                }
                                if (files && files[0]) {
                                    for(i = 0; i < files.length; i++) {
                                        message = '".htmlspecialchars($this->msg)."';
                                        var file = files[i];
                                        message = message.replace(':file', file.name);
                                        if ( (/\.(png|jpeg|jpg|gif|svg)$/i).test(file.name) ) {
                                            readImage(file, '$key', '$val_condition', message, 'min_width');
                                        } else {
                                            error_files = true;
                                            errors = true;
                                            error_box.closest('.form-group').removeClass('has-success');
                                            error_box.addClass('text-danger');
                                            error_box.closest('.form-group').addClass('has-error');
                                        }
                                    }
                                }
                                if (errors) {
                                    error_files = true;
                                } else {
                                    error_files = false;
                                }
                              });\n";
                        array_push($this->clientValidation, $js);
                        break;
                        
                    case "img_max_width":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["img_max_width"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        $js =  "window.URL = window.URL || window.webkitURL;
                                elBrowse = document.getElementById('".$key."');
                                useBlob = false && window.URL;
                                elBrowse.addEventListener('change', function() {
                                var files  = this.files;
                                var errors = false;
                                error_box = $('#error_".$key."');
                                error_box.html('');
                                if (!files) {
                                    errors = true;
                                }
                                if (files && files[0]) {
                                    for(i = 0; i < files.length; i++) {
                                        message = '".htmlspecialchars($this->msg)."';
                                        var file = files[i];
                                        message = message.replace(':file', file.name);
                                        if ( (/\.(png|jpeg|jpg|gif|svg)$/i).test(file.name) ) {
                                            readImage(file, '$key', '$val_condition', message, 'max_width');
                                        } else {
                                            error_files = true;
                                            errors = true;
                                            error_box.closest('.form-group').removeClass('has-success');
                                            error_box.addClass('text-danger');
                                            error_box.closest('.form-group').addClass('has-error');
                                        }
                                    }
                                }
                                if (errors) {
                                    error_files = true;
                                } else {
                                    error_files = false;
                                }
                              });\n";
                        array_push($this->clientValidation, $js);
                        break;
                        
                    case "img_min_height":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["img_min_height"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        $js =  "window.URL = window.URL || window.webkitURL;
                                elBrowse = document.getElementById('".$key."');
                                useBlob = false && window.URL;
                                elBrowse.addEventListener('change', function() {
                                var files  = this.files;
                                var errors = false;
                                error_box = $('#error_".$key."');
                                error_box.html('');
                                if (!files) {
                                    errors = true;
                                }
                                if (files && files[0]) {
                                    for(i = 0; i < files.length; i++) {
                                        message = '".htmlspecialchars($this->msg)."';
                                        var file = files[i];
                                        message = message.replace(':file', file.name);
                                        if ( (/\.(png|jpeg|jpg|gif|svg)$/i).test(file.name) ) {
                                            readImage(file, '$key', '$val_condition', message, 'min_height');
                                        } else {
                                            error_files = true;
                                            errors = true;
                                            error_box.closest('.form-group').removeClass('has-success');
                                            error_box.addClass('text-danger');
                                            error_box.closest('.form-group').addClass('has-error');
                                        }
                                    }
                                }
                                if (errors) {
                                    error_files = true;
                                } else {
                                    error_files = false;
                                }
                              });\n";
                        array_push($this->clientValidation, $js);
                        break;
                        
                    case "img_max_height":
                        if (array_key_exists($key.".".$arg_condition, $messages)) {
                            $this->msg = str_replace($search, $replace, $messages[$key.".".$arg_condition]);
                        } else {
                            $string = $this->validation_translate[$this->lang]["img_max_height"];
                            $this->msg = str_replace($search, $replace, $string);
                        }
                        $js =  "window.URL = window.URL || window.webkitURL;
                                elBrowse = document.getElementById('".$key."');
                                useBlob = false && window.URL;
                                elBrowse.addEventListener('change', function() {
                                var files  = this.files;
                                var errors = false;
                                error_box = $('#error_".$key."');
                                error_box.html('');
                                if (!files) {
                                    errors = true;
                                }
                                if (files && files[0]) {
                                    for(i = 0; i < files.length; i++) {
                                        message = '".htmlspecialchars($this->msg)."';
                                        var file = files[i];
                                        message = message.replace(':file', file.name);
                                        if ( (/\.(png|jpeg|jpg|gif|svg)$/i).test(file.name) ) {
                                            readImage(file, '$key', '$val_condition', message, 'max_height');
                                        } else {
                                            error_files = true;
                                            errors = true;
                                            error_box.closest('.form-group').removeClass('has-success');
                                            error_box.addClass('text-danger');
                                            error_box.closest('.form-group').addClass('has-error');
                                        }
                                    }
                                }
                                if (errors) {
                                    error_files = true;
                                } else {
                                    error_files = false;
                                }
                              });\n";
                        array_push($this->clientValidation, $js);
                        break;
                    }
            }
        }
        $iterator++;
    }
    
    public function isValid()
    {
        if (count($this->error_messages) > 0) {
            return false;
        }
        return true;
    }
    
    public function getErrors()
    {
        return $this->error_messages;
    }
    
    public function getFirstError()
    {
        if (count($this->getErrors()) > 0) {
            $errors = $this->getErrors();
            return reset($errors);
        }
    }
    
    protected function errorInCollection($field)
    {
        foreach (array_keys($this->error_messages) as $key) {
            if ($key == $field) {
                return true;
                break;
            }
        }
        return false;
    }
    
    public function attributes($fields = array())
    {
        foreach ($fields as $field => $value) {
            $this->attributes[$field] = $value;
        }
    }
    
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    public function getClientValidation($form)
    {
        $js = "<script>\n";
        $js .= "$(function(){\n";
        foreach (array_keys($this->clientValidationEvents) as $index) {
            foreach(array_keys($this->clientValidationEvents[$index]) as $key) {
                foreach(array_keys($this->clientValidationEvents[$index][$key]) as $event) {
                    $js .= "$('#$key').on('$event', function(){\n";
                    foreach($this->clientValidationEvents[$index][$key][$event] as $validation) {
                        $js .= "if ($validation == false) {e.preventDefault();return;}\n";
                    }
                    $js .= "});\n";
                }
            }
        }
        foreach ($this->clientValidation as $validation) {
            if (preg_match("/window\.URL/", $validation)) {
                $js .= $validation;
            }
        }
        $js .= "$('$form').on('submit', function(e){\n";
        foreach ($this->clientValidation as $validation) {
            if (!preg_match("/window\.URL/", $validation)) {
                $js .= "if ($validation == false) {e.preventDefault();return;}\n";
            }
        }
        $js .= "if (error_files) {e.preventDefault();return;}\n";
        $js .= "$(this).off('submit');\n";
        $js .= "});\n";
        $js .= "});\n";
        $js .= "</script>\n";
        return $js;
    }
    
    public function translate($lang)
    {
        return $this->lang = $lang;
    }
}
