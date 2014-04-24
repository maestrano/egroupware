<?php

/**
 * Mno Organization Class
 */
class MnoSoaOrganization extends MnoSoaBaseOrganization
{
    protected $_local_entity_name = "CONTACT_ORG";
    
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
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start " . $this->_id);
        
	if (!empty($this->_id)) {            
	    $local_id = $this->getLocalIdByMnoId($this->_id);
            $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
            
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
    
    // DONE
    protected function pushName() 
    {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_name = $this->push_set_or_delete_value($this->_local_entity['org_name']);
	$this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end " . $this->_name);
    }
    
    // DONE
    protected function pullName() 
    {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity['org_name'] = $this->pull_set_or_delete_value($this->_name);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushIndustry() {
	// DO NOTHING
    }
    
    // DONE
    protected function pullIndustry() {
	// DO NOTHING
    }
    
    // DONE
    protected function pushAnnualRevenue() {
	// DO NOTHING
    }
    
    // DONE
    protected function pullAnnualRevenue() {
	// DO NOTHING
    }
    
    // DONE
    protected function pushCapital() {
        // DO NOTHING
    }
    
    // DONE
    protected function pullCapital() {
        // DO NOTHING
    }
    
    // DONE
    protected function pushNumberOfEmployees() {
	// DO NOTHING
    }
    
    // DONE
    protected function pullNumberOfEmployees() {
       // DO NOTHING
    }
    
    // DONE
    protected function pushAddresses() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        // ADDRESS 1 -> POSTAL ADDRESS
        $this->_address->postalAddress->streetAddress = trim($this->push_set_or_delete_value($this->_local_entity['adr_one_street']) . ' ' . $this->push_set_or_delete_value($this->_local_entity['adr_one_street2']));
        $this->_address->postalAddress->locality = $this->push_set_or_delete_value($this->_local_entity['adr_one_locality']);
        $this->_address->postalAddress->region = $this->push_set_or_delete_value($this->_local_entity['adr_one_region']);
        $this->_address->postalAddress->postalCode = $this->push_set_or_delete_value($this->_local_entity['adr_one_postalcode']);
        $this->_address->postalAddress->country = strtoupper($this->push_set_or_delete_value($this->_local_entity['adr_one_countrycode']));
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullAddresses() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
	// POSTAL ADDRESS -> ADDRESS 1
        $street_address = $this->pull_set_or_delete_value($this->_address->postalAddress->streetAddress);
        
        if (strlen($street_address) > 63) {
            $ww = wordwrap($street_address, 63, "\n", true);
            $pieces = explode(" ", $ww);
            $this->_local_entity['adr_one_street'] = $pieces[0];
            $this->_local_entity['adr_one_street2'] = $pieces[1];
        } else {
            $this->_local_entity['adr_one_street'] = $street_address;
            $this->_local_entity['adr_one_street2'] = "";
        }
        
        $this->_local_entity['adr_one_locality'] = $this->pull_set_or_delete_value($this->_address->postalAddress->locality);
        $this->_local_entity['adr_one_region'] = $this->pull_set_or_delete_value($this->_address->postalAddress->region);
        $this->_local_entity['adr_one_postalcode'] = $this->pull_set_or_delete_value($this->_address->postalAddress->postalCode);
        $this->_local_entity['adr_one_countrycode'] = $this->pull_set_or_delete_value($this->_address->postalAddress->country);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushEmails() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_email->emailAddress = $this->push_set_or_delete_value($this->_local_entity['email']);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullEmails() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity['email'] = $this->pull_set_or_delete_value($this->_email->emailAddress);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushTelephones() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_telephone->voice = $this->push_set_or_delete_value($this->_local_entity['tel_work']);
        $this->_telephone->fax = $this->push_set_or_delete_value($this->_local_entity['tel_fax']);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullTelephones() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity['tel_work'] = $this->pull_set_or_delete_value($this->_telephone->voice);
        $this->_local_entity['tel_fax'] = $this->pull_set_or_delete_value($this->_telephone->fax);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushWebsites() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_website->url = $this->push_set_or_delete_value($this->_local_entity['url']);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullWebsites() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity['url'] = $this->pull_set_or_delete_value($this->_website->url);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushEntity() {
        // DO NOTHING
    }
    
    // DONE
    protected function pullEntity() {
        // DO NOTHING
    }
    
    // DONE
    protected function saveLocalEntity($push_to_maestrano, $status) {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $contact = new addressbook_bo();
        $contact->save($this->_local_entity, true, false);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    public function getLocalEntityIdentifier() {
        return $this->_local_entity['id'];
    }
}

?>