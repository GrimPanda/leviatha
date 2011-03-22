<?php
    class script {
        public function init() {
            $query = "
                SELECT *
                FROM raw_loot_properties
            ";
            F3::sql($query);
            $result = F3::get('DB.result');
            
            
            foreach($result as $row) {
            
                $name = $row["name"];
                
                foreach($row as $k => $v) {
                
                    if ($k == "name") { continue; }
                    if ($v == 0) { continue; }
                    if (!is_numeric($v)) { continue; }
                    
                    echo $name . "\t" . strtolower($k) . "\t" . $v . "\n<br>";
                }
            }
        }
        
        public function integrity() {
        
            $query = "
                SELECT loot_properties.property
                FROM loot_properties
                    LEFT JOIN translate_loot_properties
                        ON translate_loot_properties.property = loot_properties.property
                WHERE translate_loot_properties.property IS NULL
            ";
            
            F3::sql($query);
            
            foreach(F3::get('DB.result') as $k => $v) {
                echo $v["property"] . "<br>";
            }
        }
        
        public function magic_parse() {
            $query = "
                SELECT *
                FROM raw_loot_properties_set_full
            ";
            F3::sql($query);
            $result = F3::get('DB.result');
            
            foreach($result as $k => $v) {
                
                $full = 0;
            
                for ($i = 1; $i <= 11; $i++) {
                    if (!empty($v['PCode' . $i . 'a'])) {
                        echo "\"" . $v["index"] . "\"\t\"" . $v['PCode' . $i . 'a'] . "\"\t\"" . $v['PParam' . $i . 'a'] . "\"\t" . $v['PMin' . $i . 'a'] . "\t" . $v['PMax' . $i . 'a'] . "\t" . $i . "<br>";
                        $full++;
                    }
                }
                for ($i = 1; $i <= 11; $i++) {
                    if (!empty($v['PCode' . $i . 'b'])) {
                        echo "\"" . $v["index"] . "\"\t\"" . $v['PCode' . $i . 'b'] . "\"\t\"" . $v['PParam' . $i . 'b'] . "\"\t" . $v['PMin' . $i . 'b'] . "\t" . $v['PMax' . $i . 'b'] . "\t" . $i . "<br>";
                        $full++;
                    }
                }
                for ($i = 1; $i <= 11; $i++) {
                    if (!empty($v['FCode' . $i])) {
                        echo "\"" . $v["index"] . "\"\t\"" . $v['FCode' . $i] . "\"\t\"" . $v['FParam' . $i] . "\"\t" . $v['FMin' . $i] . "\t" . $v['FMax' . $i] . "\t1337<br>";
                    }
                }
            }
        }
        
        public function translate() {
            $string = "Reduces damage by (@min - @max)";
            $min = 5;
            $max = 9;
            $parameter = 1;
            
            $string = str_replace("@min", $min, $string);
            $string = str_replace("@max", $max, $string);
            $string = str_replace("@param", $parameter, $string);
            
            echo $string;
        }
    }