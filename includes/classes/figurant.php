<?php

class figurant{
    public function getAll(){
        $stmt = database::$conn->prepare("SELECT * FROM ecc_characters WHERE status LIKE 'figurant%'");
		$res = $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        return $res;
    }

    public function get($id, $needle){
        if($needle == "card_id"){
            $stmt = database::$conn->prepare("SELECT * FROM ecc_characters WHERE card_id = ? AND status LIKE 'figurant%'");
            $res = $stmt->execute(array($id));
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($res == null){
                $sHex = dechex($id);
                $aDec = str_split($sHex, 2);
                if(!isset($aDec[1])){
                    return "false";
                }
                $sDec = "%".$aDec[3].$aDec[2].$aDec[1].$aDec[0]."%";
                if($sDec == "%0%"){
                    return "false";
                }
                
                $stmt = database::$conn->prepare("SELECT * FROM ecc_characters WHERE card_id LIKE ? AND status LIKE 'figurant%'");
                $res = $stmt->execute(array($sDec));
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }elseif($needle == "accountID"){
            $stmt = database::$conn->prepare("SELECT * FROM ecc_characters where $needle = ? AND sheet_status = 'active' AND status LIKE 'figurant%'");
            $res = $stmt->execute(array($id));
            $res = $stmt->fetch(PDO::FETCH_ASSOC); 
        }else{
            $stmt = database::$conn->prepare("SELECT * FROM ecc_characters where $needle = ? AND status LIKE 'figurant%'");
            $res = $stmt->execute(array($id));
            $res = $stmt->fetch(PDO::FETCH_ASSOC); 
        }

        return $res;
    }

    public function getSkills($id){
        $stmt = database::$conn->prepare("SELECT skill_id FROM ecc_char_skills where charID = ? ORDER BY skill_ID");
        $res = $stmt->execute(array($id));
        $aCharSkills = $stmt->fetchAll(PDO::FETCH_ASSOC); 

        $sSkillid = "";

        foreach($aCharSkills as $aCharSkill){
            $sSkillid .= $aCharSkill["skill_id"].",";
        }

        $sSkillid = rtrim($sSkillid, ",");



        $stmt = database::$conn->prepare("SELECT label, skill_index, level FROM ecc_skills_allskills WHERE skill_id IN ($sSkillid)");
        $res = $stmt->execute();
        $aCharSkills = $stmt->fetchAll(PDO::FETCH_ASSOC); 

        $aSkills = array();

        foreach($aCharSkills as $aCharSkill){
            
            $aCharSkill["skill_index"] = substr($aCharSkill["skill_index"], 0, strpos($aCharSkill["skill_index"], "_"));

            array_push($aSkills,$aCharSkill);
        }

        $stmt = database::$conn->prepare("SELECT type, skillgroup_siteindex, skillgroup_level, description FROM ecc_char_implants WHERE charID = ? AND status = 'active' AND type != 'flavour'");
        $res = $stmt->execute(array($id));
        $aImplants = $stmt->fetchAll(PDO::FETCH_ASSOC); 



        foreach($aImplants as $aImplant){
            $arr["level"] = $aImplant["skillgroup_level"];
            $arr["label"] = $aImplant["description"];
            $arr["skill_index"] = $aImplant["skillgroup_siteindex"];
            $arr["type"] = $aImplant["type"];
            array_push($aSkills,$arr);
        }


        foreach ($aSkills as $key => $item) {
            $aListedSkills[$item['skill_index']][$key] = $item;
        }

        $aSkills = array();

        foreach($aListedSkills as $aListedSkill){

            $level = array_column($aListedSkill, 'level');
            array_multisort($level, SORT_ASC, $aListedSkill);

            //var_dump($aListedSkill);
            $group = array();
            $group["name"] = "";
            $group["level"] = 0;
            $group["specialty"] = false;
            $group["sub_skills"] = array();
            
            foreach($aListedSkill as $arr){
                $array = array(
                    "name" => $arr["label"],
                    "level" => intval($arr["level"])
                );

                if(isset($arr["type"])){
                    $array["source"] = $arr["type"];
                }

                array_push($group["sub_skills"], $array);

                $group["name"] = $arr["skill_index"];

                if(!isset($arr["type"])){
                    if($arr["level"] > $group["level"]){
                        $group["level"] = intval($arr["level"]);
                    }
                }

                
            }

            if($group["level"] > 5){
                $group["specialty"] = true;
            }

            array_push($aSkills, $group);

            
        }

        return $aSkills;
    }

    function addFigurant($character){
        $check = $this->checkCardId($character["card_id"]);

        $character_name     = $character["character_name"];
        $card_id            = $character["card_id"];
        $faction            = $character["faction"];
        $rank               = $character["rank"];
        $threat_assessment  = $character["threat_assessment"];
        $douane_disposition = $character["douane_disposition"];
        $douane_notes       = $character["douane_notes"];
        $bastion_clearance  = $character["bastion_clearance"];
        $ICC_number         = $character["ICC_number"];
        $bloodtype          = $character["bloodtype"];
        $ic_birthday        = $character["ic_birthday"];
        $homeplanet         = $character["homeplanet"];

        $figustatus = "figurant";
        if(isset($character["recurring"])){
            $figustatus = "figurant-recurring";
        }

        if(!$check){

            $stmt = database::$conn->prepare(
                "INSERT into ecc_characters
                    (accountID, character_name, card_id, faction, status, rank, threat_assessment, douane_disposition, douane_notes, bastion_clearance, ICC_number, bloodtype, ic_birthday, homeplanet)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
            $res = $stmt->execute(array(
                0, $character_name, $card_id, $faction, $figustatus, $rank, $threat_assessment, $douane_disposition, $douane_notes, $bastion_clearance, $ICC_number, $bloodtype, $ic_birthday, $homeplanet
            ));

            return database::$conn->lastInsertId();

        }else{
            if($check["status"] == "figurant-recurring"){

                $stmt = database::$conn->prepare("UPDATE ecc_characters SET card_id=? WHERE characterID = ?");
                $res = $stmt->execute(array(NULL, $check["characterID"]));

                $stmt = database::$conn->prepare(
                    "INSERT into ecc_characters
                        (accountID, character_name, card_id, faction, status, rank, threat_assessment, douane_disposition, douane_notes, bastion_clearance, ICC_number, bloodtype, ic_birthday, homeplanet)
                    VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                $res = $stmt->execute(array(
                    0, $character_name, $card_id, $faction, $figustatus, $rank, $threat_assessment, $douane_disposition, $douane_notes, $bastion_clearance, $ICC_number, $bloodtype, $ic_birthday, $homeplanet
                ));

                return database::$conn->lastInsertId();

            }else{

                $stmt = database::$conn->prepare(
                    "UPDATE ecc_characters SET
                        character_name=?,
                        faction=?,
                        status=?,
                        rank=?,
                        threat_assessment=?,
                        douane_disposition=?,
                        douane_notes=?,
                        bastion_clearance=?,
                        ICC_number=?,
                        bloodtype=?,
                        ic_birthday=?,
                        homeplanet=?
                    WHERE characterID = ?");
                $res = $stmt->execute(array($character_name, $faction, $figustatus, $rank, $threat_assessment, $douane_disposition, $douane_notes, $bastion_clearance, $ICC_number, $bloodtype, $ic_birthday, $homeplanet, $check["characterID"]));

                return "success";
            }
        }

    }

    private function checkCardId($cardId){
        $stmt = database::$conn->prepare("SELECT * FROM ecc_characters WHERE card_id = ?");
        $res = $stmt->execute(array($cardId));
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return $res;
    }
}
