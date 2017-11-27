<?php

        $output = null;

        if (!(isset($Relay_Agent_Infos))) {
                if (isset($argv[1])) {
                        $Relay_Agent_Infos = $argv;
                        unset($Relay_Agent_Infos[0]);
                        echo "\nDHCPDECODE:\n";
                }else{
                        $output = $output . "\nNo DHCPDECODE set..\n";
                        $output = $output . "Example 01-0F-41-56-43-30-30-30-30-33-31-30-35-38-30-31-33-09-11-00-00-0D-E9-0C-81-04-00-00-08-0B-82-04-00-00-35-FB\n";
                        $Relay_Agent_Infos = array("01-0F-41-56-43-30-30-30-30-33-31-30-35-38-30-31-33-09-11-00-00-0D-E9-0C-81-04-00-00-08-0B-82-04-00-00-35-FB");
                }
        }

        foreach ($Relay_Agent_Infos as $Relay_Agent_Info){
                $Full_Relay_Agent_Info_data = str_replace("-","",$Relay_Agent_Info);
                $Full_Relay_Agent_Info_Length = strlen($Full_Relay_Agent_Info_data);
                $Process_Relay_Agent_Info_data = $Full_Relay_Agent_Info_data;
                $Process_Relay_Agent_Info_data_Length = $Full_Relay_Agent_Info_Length;
                $output = $output . "\nDecoding: $Full_Relay_Agent_Info_data\n";

                if(hexdec(substr($Process_Relay_Agent_Info_data,0,2)) == 1){
                        $option_number = sprintf('%02d',hexdec(substr($Process_Relay_Agent_Info_data,0,2)));
                        $option_data_length = sprintf('%02d',hexdec(substr($Process_Relay_Agent_Info_data,2,2)));
                        $option_data = substr($Process_Relay_Agent_Info_data,4,($option_data_length*2));
                        $option_data_decode = hex2bin($option_data);
                        $output = $output . "\tOption $option_number Data: $option_data\n";
                        $output = $output . "\tOption $option_number Data Decode: $option_data_decode\n";
                        $Process_Relay_Agent_Info_data = substr($Process_Relay_Agent_Info_data,4 + ($option_data_length*2));
                        $Process_Relay_Agent_Info_data_Length = strlen($Process_Relay_Agent_Info_data);
                }else{
                        $output = $output . "Not valid\n";
                        exit;
                }
                while ($Process_Relay_Agent_Info_data_Length > 0){
                        $option_number = sprintf('%02d',hexdec(substr($Process_Relay_Agent_Info_data,0,2)));
                        $option_data_length = hexdec(substr($Process_Relay_Agent_Info_data,2,2));
                        $option_data = substr($Process_Relay_Agent_Info_data,4,($option_data_length*2));
                        $output = $output . "\tOption $option_number Data: $option_data\n";

                        if ($option_number == 9){
                                $option_09_vendor_code = substr($option_data,0,8);
                                $option_09_length = sprintf('%02d',hexdec(substr($option_data,8,2)));
                                $option_09_data = substr($option_data,10,($option_09_length*2));
                                $output = $output . "\tOption 09 Decode:\n";
                                $Process_Option09_data = $option_09_data;
                                $Process_Option09_data_length = strlen($Process_Option09_data);
                                while ($Process_Option09_data_length > 0){
                                        $suboption09_number = sprintf('%02d',hexdec(substr($Process_Option09_data,0,2)));
                                        $suboption09_data_length =  hexdec(substr($Process_Option09_data,2,2));
                                        $suboption09_data = substr($Process_Option09_data,4,($suboption09_data_length*2));
                                        if($suboption09_number == 129 || $suboption09_number == 130){
                                                $suboption09_data_decode = hexdec($suboption09_data);
                                                $output = $output . "\t\tSubOption $suboption09_number Data Decode: $suboption09_data_decode\n";
                                                $output = $output . "\t\tSubOption $suboption09_number Data Decode Clean: ". round($suboption09_data_decode / 1000,2) . "M\n";
                                        }
                                        $Process_Option09_data = substr($Process_Option09_data,4+($suboption09_data_length*2));
                                        $Process_Option09_data_length = strlen($Process_Option09_data);
                                }
                        }

                        $Process_Relay_Agent_Info_data = substr($Process_Relay_Agent_Info_data,4+($option_data_length*2));
                        $Process_Relay_Agent_Info_data_Length = strlen($Process_Relay_Agent_Info_data);
                }
        }
        echo "$output";
?>
