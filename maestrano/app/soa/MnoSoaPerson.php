<?php

/**
 * Mno Organization Class
 */
class MnoSoaPerson extends MnoSoaBasePerson
{
    protected $_local_entity_name = "CONTACT_PER";
    
    // DONE
    protected function pushId() 
    {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start");
	$id = $this->getLocalEntityIdentifier();
	
	if (!empty($id)) {
	    $mno_id = $this->getMnoIdByLocalId($id);

	    if ($this->isValidIdentifier($mno_id)) {
                $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " this->getMnoIdByLocalId(id) = " . json_encode($mno_id));
		$this->_id = $mno_id->_id;
	    }
	}
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end");
    }
    
    // DONE
    protected function pullId() 
    {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start");
	if (!empty($this->_id)) {
	    $local_id = $this->getLocalIdByMnoId($this->_id);
            $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
	    
	    if ($this->isValidIdentifier($local_id)) {
                $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " is STATUS_EXISTING_ID");
                $contact = new addressbook_bo();
                $this->_local_entity = $contact->read((int) $local_id->_id, true);
                if (empty($this->_local_entity['owner'])) {
                    $this->_local_entity['owner'] = "-1";
                }
		return constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');
	    } else if ($this->isDeletedIdentifier($local_id)) {
                $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " is STATUS_DELETED_ID");
                return constant('MnoSoaBaseEntity::STATUS_DELETED_ID');
            } else {
                $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " is STATUS_NEW_ID");
                $this->_local_entity = array();
                $this->pullName();
                $contact = new addressbook_bo();
                if (empty($this->_local_entity['owner'])) {
                    $this->_local_entity['owner'] = "-1";
                }
                $id = $contact->save($this->_local_entity, true, false);
                $this->_local_entity = $contact->read((int) $id, true);
		return constant('MnoSoaBaseEntity::STATUS_NEW_ID');
	    }
	}
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " return STATUS_ERROR");
        return constant('MnoSoaBaseEntity::STATUS_ERROR');
    }
    
    protected function pushName() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_name->familyName = $this->push_set_or_delete_value($this->_local_entity['n_family']);
        $this->_name->givenNames = $this->push_set_or_delete_value($this->_local_entity['n_given']);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    
    
    protected function pullName() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity['n_family'] = $this->pull_set_or_delete_value($this->_name->familyName);
        $this->_local_entity['n_given'] = $this->pull_set_or_delete_value($this->_name->givenNames);
        $this->_local_entity['n_fn'] = preg_replace('!\s+!', ' ', trim($this->_local_entity['n_prefix'] . " " .
                                       $this->_local_entity['n_given'] . " " .
                                       $this->_local_entity['n_middle'] . " " .
                                       $this->_local_entity['n_family'] . " " .
                                       $this->_local_entity['n_suffix']));
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pushBirthDate() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_birth_date = $this->push_set_or_delete_value($this->_local_entity['bday']);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pullBirthDate() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity['bday'] = $this->pull_set_or_delete_value($this->_birth_date);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pushGender() {
	// DO NOTHING
    }
    
    protected function pullGender() {
	// DO NOTHING
    }
    
    protected function pushAddresses() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");       
        // PRIVATE ADDRESS -> HOME POSTAL ADDRESS
        $this->_address->home->postalAddress->streetAddress = trim($this->push_set_or_delete_value($this->_local_entity['adr_two_street']) . ' ' . $this->push_set_or_delete_value($this->_local_entity['adr_two_street2']));
        $this->_address->home->postalAddress->locality = $this->push_set_or_delete_value($this->_local_entity['adr_two_locality']);
        $this->_address->home->postalAddress->region = $this->push_set_or_delete_value($this->_local_entity['adr_two_region']);
        $this->_address->home->postalAddress->postalCode = $this->push_set_or_delete_value($this->_local_entity['adr_two_postalcode']);
        $this->_address->home->postalAddress->country = strtoupper($this->push_set_or_delete_value($this->_local_entity['adr_two_countrycode']));
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pullAddresses() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");      
        // HOME POSTAL ADDRESS -> PRIVATE ADDRESS
        $home_street_address = $this->pull_set_or_delete_value($this->_address->home->postalAddress->streetAddress);
        
        if (strlen($home_street_address) > 63) {
            $ww = wordwrap($home_street_address, 63, "\n", true);
            $pieces = explode(" ", $ww);
            $this->_local_entity['adr_two_street'] = $pieces[0];
            $this->_local_entity['adr_two_street2'] = $pieces[1];
        } else {
            $this->_local_entity['adr_two_street'] = $home_street_address;
            $this->_local_entity['adr_two_street2'] = "";
        }
        
        $this->_local_entity['adr_two_locality'] = $this->pull_set_or_delete_value($this->_address->home->postalAddress->locality);
        $this->_local_entity['adr_two_region'] = $this->pull_set_or_delete_value($this->_address->home->postalAddress->region);
        $this->_local_entity['adr_two_postalcode'] = $this->pull_set_or_delete_value($this->_address->home->postalAddress->postalCode);
        $this->_local_entity['adr_two_countrycode'] = $this->pull_set_or_delete_value($this->_address->home->postalAddress->country);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pushEmails() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_email->emailAddress = $this->push_set_or_delete_value($this->_local_entity['email_home']);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pullEmails() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity['email_home'] = $this->pull_set_or_delete_value($this->_email->emailAddress);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    
    protected function pushTelephones() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_telephone->home->voice = $this->push_set_or_delete_value($this->_local_entity['tel_home']);
        $this->_telephone->home->mobile = $this->push_set_or_delete_value($this->_local_entity['tel_cell']);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pullTelephones() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity['tel_home'] = $this->pull_set_or_delete_value($this->_telephone->home->voice);
        $this->_local_entity['tel_cell'] = $this->pull_set_or_delete_value($this->_telephone->home->mobile);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pushWebsites() {
	$this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_website->url = $this->push_set_or_delete_value($this->_local_entity['url_home']);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pullWebsites() {
	$this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity['url_home'] = $this->pull_set_or_delete_value($this->_website->url);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pushEntity() {
        // DO NOTHING
    }
    
    protected function pullEntity() {
        // DO NOTHING
    }

    protected function pushRole() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        
        $local_org_id = $this->getLocalEntityIdentifier();
        
        if (empty($this->_local_entity['org_name'])) {
            $this->_role = (object) array();
            
            $mno_org_id = $this->getMnoIdByLocalIdName($local_org_id, "CONTACT_ORG");

            if ($this->isValidIdentifier($mno_org_id)) {
                    $organization = new MnoSoaOrganization($this->_db, $this->_log);		
                    $organization->sendDeleteNotification($local_org_id);
            }

            return;
        }
        
        $mno_org_id = $this->getMnoIdByLocalIdName($local_org_id, "CONTACT_ORG");

        if ($this->isValidIdentifier($mno_org_id)) {    
            $this->_log->debug("is valid identifier");

            $org_contact = new addressbook_bo();
            $org_local_entity = $org_contact->read((int) $local_org_id, true);

            $organization = new MnoSoaOrganization($this->_db, $this->_log);		
            $status = $organization->send($org_local_entity);

            $this->_role->organization->id = $mno_org_id->_id;
        } else if ($this->isDeletedIdentifier($mno_org_id)) {
            $this->_log->debug(__FUNCTION__ . " deleted identifier");
            // do not update
            return;
        } else {
            $this->_log->debug("before contacts find by id=" . json_encode($local_org_id));
            $org_contact = new addressbook_bo();
            $org_local_entity = $org_contact->read((int) $local_org_id, true);

            $organization = new MnoSoaOrganization($this->_db, $this->_log);		
            $status = $organization->send($org_local_entity);
            $this->_log->debug("after mno soa organization send");

            if ($status) {
                $mno_org_id = $this->getMnoIdByLocalIdName($local_org_id, "CONTACT_ORG");

                if ($this->isValidIdentifier($mno_org_id)) {
                    $this->_role->organization->id = $mno_org_id->_id;
                }
            }
        }
        
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pullRole() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        if (empty($this->_role->organization->id)) {
            // DO NOTHING - RETURN
            return;
        }
        
        $mno_org_id = $this->_role->organization->id;
        $local_org_id = $this->getLocalIdByMnoIdName($mno_org_id, "ORGANIZATIONS");
        $local_person_id = $this->getLocalEntityIdentifier();
        
        if ($this->isValidIdentifier($local_org_id)) {
            if (empty($local_person_id)) {
                $this->setLocalEntityIdentifier($local_org_id->_id);
            } else if ($local_org_id->_id != $local_person_id) {
                throw new Exception("Organization " . $local_org_id->_id . " and person " . $local_person_id . " must be under the same contact");
            }
            
            $notification->entity = "ORGANIZATIONS";
            $notification->id = $mno_org_id;
            
            $organization = new MnoSoaOrganization($this->_db, $this->_log);
            $status = $organization->receiveNotification($notification);
        } else if ($this->isDeletedIdentifier($local_org_id)) {
            // do not update
            return;
        } else {
            $notification->entity = "ORGANIZATIONS";
            $notification->id = $this->_role->organization->id;
            
            $organization = new MnoSoaOrganization($this->_db, $this->_log);
            
            if (!empty($local_person_id)) {
                $organization->addIdMapEntry($local_person_id, $mno_org_id);
            }
            
            $status = $organization->receiveNotification($notification);
        }
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function saveLocalEntity($push_to_maestrano, $status) {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_log->debug(__CLASS__ . " " . __FUNCTION__ . ": local_entity=" . json_encode($this->_local_entity));
        $contact = new addressbook_bo();
        $contact->save($this->_local_entity, true, false);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    public function getLocalEntityIdentifier() {
        return $this->_local_entity['id'];
    }
    
    public function setLocalEntityIdentifier($id) {
        $this->_local_entity['id'] = $id;
    }
}

?>