<?php

class ManageController extends My_Controller_Action
{
    private $data;

    public function salesTeamAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sales-team'.DIRECTORY_SEPARATOR.'sales-team.php';
    }

    public function salesTeamCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sales-team'.DIRECTORY_SEPARATOR.'sales-team-create.php';
    }

    public function salesTeamSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sales-team'.DIRECTORY_SEPARATOR.'sales-team-save.php';
    }

    public function salesTeamDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sales-team'.DIRECTORY_SEPARATOR.'sales-team-del.php';

    }

    public function departmentAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'department'.DIRECTORY_SEPARATOR.'department.php';
    }

    public function departmentCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'department'.DIRECTORY_SEPARATOR.'department-create.php';

    }

    public function departmentSaveAction()
    {

        require_once 'manage'.DIRECTORY_SEPARATOR.'department'.DIRECTORY_SEPARATOR.'department-save.php';

    }

    public function departmentDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'department'.DIRECTORY_SEPARATOR.'department-del.php';

    }

    public function religionAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'religion'.DIRECTORY_SEPARATOR.'religion.php';

    }

    public function religionCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'religion'.DIRECTORY_SEPARATOR.'religion-create.php';

    }

    public function religionSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'religion'.DIRECTORY_SEPARATOR.'religion-save.php';
    }

    public function religionDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'religion'.DIRECTORY_SEPARATOR.'religion-del.php';

    }

    public function nationalityAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'nationality'.DIRECTORY_SEPARATOR.'nationality.php';

    }

    public function nationalityCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'nationality'.DIRECTORY_SEPARATOR.'nationality-create.php';

    }

    public function nationalitySaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'nationality'.DIRECTORY_SEPARATOR.'nationality-save.php';

    }

    public function nationalityDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'nationality'.DIRECTORY_SEPARATOR.'nationality-del.php';

    }

    public function storeStaffAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-staff'.DIRECTORY_SEPARATOR.'list.php';
    }
    
    public function storeStaffCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-staff'.DIRECTORY_SEPARATOR.'create.php';
    }

    public function storeStaffSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-staff'.DIRECTORY_SEPARATOR.'save.php';
    }

    public function storeStaffDeleteAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-staff'.DIRECTORY_SEPARATOR.'delete.php';

    }

    public function storeStaffLogAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-staff-log'.DIRECTORY_SEPARATOR.'list.php';
    }

    public function storeStaffLogUpdateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-staff-log'.DIRECTORY_SEPARATOR.'update.php';

    }
    public function storeStaffLogDeleteAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-staff-log'.DIRECTORY_SEPARATOR.'delete.php';

    }


    /*********************** PROVINCE **************************************/

    public function provinceAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'province'.DIRECTORY_SEPARATOR.'list.php';
    }

    public function provinceCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'province'.DIRECTORY_SEPARATOR.'create.php';
    }

    public function provinceSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'province'.DIRECTORY_SEPARATOR.'save.php';
    }

    public function provinceDeleteAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'province'.DIRECTORY_SEPARATOR.'delete.php';
    }

    /*********************** END PROVINCE ***********************************/

    /*********************** DISTRICT **************************************/

    public function districtAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'district'.DIRECTORY_SEPARATOR.'list.php';
    }

    public function districtCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'district'.DIRECTORY_SEPARATOR.'create.php';
    }

    public function districtSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'district'.DIRECTORY_SEPARATOR.'save.php';
    }

    public function districtDeleteAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'district'.DIRECTORY_SEPARATOR.'delete.php';
    }

    /*********************** END DISTRICT ***********************************/

    public function subDistrictAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sub-district'.DIRECTORY_SEPARATOR.'list.php';
    }

    public function subDistrictCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sub-district'.DIRECTORY_SEPARATOR.'create.php';
    }

    public function subDistrictSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sub-district'.DIRECTORY_SEPARATOR.'save.php';
    }

    public function subDistrictDeleteAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sub-district'.DIRECTORY_SEPARATOR.'delete.php';
    }

    /*********************** END SUB DISTRICT ***********************************/

    /*********************** SUB area **************************************/

    public function subAreaAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sub-area'.DIRECTORY_SEPARATOR.'list.php';
    }

    public function subAreaCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sub-area'.DIRECTORY_SEPARATOR.'create.php';
    }

    public function subAreaSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sub-area'.DIRECTORY_SEPARATOR.'save.php';
    }

    public function subAreaDeleteAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sub-area'.DIRECTORY_SEPARATOR.'delete.php';
    }

    /*********************** End SUB area **************************************/

    public function titleAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'title'.DIRECTORY_SEPARATOR.'title.php';

    }

    public function titleCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'title'.DIRECTORY_SEPARATOR.'title-create.php';

    }

    public function titleSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'title'.DIRECTORY_SEPARATOR.'title-save.php';

    }

    public function titleDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'title'.DIRECTORY_SEPARATOR.'title-del.php';

    }

    public function teamAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'team'.DIRECTORY_SEPARATOR.'team.php';

    }

    public function teamCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'team'.DIRECTORY_SEPARATOR.'team-create.php';

    }

    public function teamSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'team'.DIRECTORY_SEPARATOR.'team-save.php';

    }

    public function teamDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'team'.DIRECTORY_SEPARATOR.'team-del.php';

    }

    public function contractTermAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'contract-term'.DIRECTORY_SEPARATOR.'contract-term.php';

    }

    public function contractTermCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'contract-term'.DIRECTORY_SEPARATOR.'contract-term-create.php';

    }

    public function contractTermSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'contract-term'.DIRECTORY_SEPARATOR.'contract-term-save.php';

    }

    public function contractTermDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'contract-term'.DIRECTORY_SEPARATOR.'contract-term-del.php';

    }

    public function contractTypeAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'contract-type'.DIRECTORY_SEPARATOR.'contract-type.php';

    }

    public function contractTypeCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'contract-type'.DIRECTORY_SEPARATOR.'contract-type-create.php';

    }

    public function contractTypeSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'contract-type'.DIRECTORY_SEPARATOR.'contract-type-save.php';

    }

    public function contractTypeDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'contract-type'.DIRECTORY_SEPARATOR.'contract-type-del.php';

    }

    public function productAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'product'.DIRECTORY_SEPARATOR.'product.php';

    }

    public function productCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'product'.DIRECTORY_SEPARATOR.'product-create.php';

    }

    public function productSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'product'.DIRECTORY_SEPARATOR.'product-save.php';

    }

    public function productDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'product'.DIRECTORY_SEPARATOR.'product-del.php';

    }

    public function areaAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'area'.DIRECTORY_SEPARATOR.'area.php';

    }

    public function areaCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'area'.DIRECTORY_SEPARATOR.'area-create.php';

    }

    public function areaSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'area'.DIRECTORY_SEPARATOR.'area-save.php';

    }

    public function areaDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'area'.DIRECTORY_SEPARATOR.'area-del.php';
    }

    public function companyAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'list.php';
    }

    public function companyDeleteAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'delete.php';
    }

    public function companyEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'edit.php';
    }

    public function companyCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'create.php';
    }

    public function companySaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'save.php';
    }

    public function assignAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $staff_id    = $userStorage->id;
        
        $QArea       = new Application_Model_Area();
        $areas  = $QArea->fetchAll();

        $this->view->areas = $areas;

        $this->_helper->viewRenderer->setRender('regional-market/assign');
    }

    public function storeAction()
    {
        // $this->_helper->viewRenderer->setNoRender(true);
        // echo "<br/><div align='center'><img alt='under_construction' src='/img/under-construction-sign.png'></div><br/>";

        require_once 'manage'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.'store.php';
    }

    // Add By Khuan //

    public function storeDetailAction()
    {
        require_once 'manage' .DIRECTORY_SEPARATOR. 'store' .DIRECTORY_SEPARATOR. 'store-detail.php';
    }

    public function storeEventAction()
    {
        require_once 'manage' .DIRECTORY_SEPARATOR. 'store' .DIRECTORY_SEPARATOR. 'store-event.php';
    }

    // End Add //

    public function storeLeaderAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.'store-leader.php';

    }

    public function storeCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.'store-create.php';

    }

    public function storeEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.'store-edit.php';

    }

    public function storeSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.'store-save.php';

    }

    public function storeDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.'store-del.php';

    }

    public function storeUndelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.'store-undel.php';

    }

    public function storeUpdateSubDistAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.'store-update-sub-dist.php';

    }

    public function viewStoreStaffAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.'view-store-staff.php';

    }

    //------------------view modal
    public function storeLogDataAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.'store-log-data.php';
    }
     public function storeLogViewAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.'store-log-view.php';
    }

    public function notificationAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'notification'.DIRECTORY_SEPARATOR.'notification.php';

    }

    public function notificationCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'notification'.DIRECTORY_SEPARATOR.'notification-create.php';

    }

    public function notificationSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'notification'.DIRECTORY_SEPARATOR.'notification-save.php';

    }

    public function notificationDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'notification'.DIRECTORY_SEPARATOR.'notification-del.php';

    }

    public function notificationUploadAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'notification'.DIRECTORY_SEPARATOR.'notification-upload.php';

    }

    public function notificationCategoryAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'notification'.DIRECTORY_SEPARATOR.'notification-category.php';
    }

    public function notificationCategorySaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'notification'.DIRECTORY_SEPARATOR.'notification-category-save.php';

    }

    public function notificationCategoryEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'notification'.DIRECTORY_SEPARATOR.'notification-category-edit.php';

    }

    public function notificationCategoryDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'notification'.DIRECTORY_SEPARATOR.'notification-category-del.php';

    }

    public function notificationMassUploadAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'notification'.DIRECTORY_SEPARATOR.'mass-upload.php';
    }

    public function notificationMassUploadSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'notification'.DIRECTORY_SEPARATOR.'mass-upload-save.php';
    }

    public function dealerAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'dealer'.DIRECTORY_SEPARATOR.'dealer.php';
    }

    public function dealerSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'dealer'.DIRECTORY_SEPARATOR.'dealer-save.php';
    }

    public function dealerEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'dealer'.DIRECTORY_SEPARATOR.'dealer-edit.php';
    }

    public function storestaffLogShortReportAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.'storestaff-log-short-report.php';
    }

    /************************************** AM **************************************/
    public function amAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'am'.DIRECTORY_SEPARATOR.'am.php';
    }

    public function amEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'am'.DIRECTORY_SEPARATOR.'am-edit.php';
    }

    public function amSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'am'.DIRECTORY_SEPARATOR.'am-save.php';
    }

    /************************************** Area Control **************************************/
    public function areaControlAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'area-control'.DIRECTORY_SEPARATOR.'area-control.php';
    }

    public function areaControlCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'area-control'.DIRECTORY_SEPARATOR.'area-control-create.php';
    }

    public function areaControlInsertAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'area-control'.DIRECTORY_SEPARATOR.'area-control-insert.php';
    }

    public function areaControlEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'area-control'.DIRECTORY_SEPARATOR.'area-control-edit.php';
    }

    public function areaControlSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'area-control'.DIRECTORY_SEPARATOR.'area-control-save.php';
    }

    // Manage : Market Name
    public function marketNameAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'market-name'.DIRECTORY_SEPARATOR.'market-name.php';
    }

    public function marketNameEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'market-name'.DIRECTORY_SEPARATOR.'market-name-edit.php';
    }

    public function marketNameCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'market-name'.DIRECTORY_SEPARATOR.'market-name-create.php';
    }

    public function marketNameSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'market-name'.DIRECTORY_SEPARATOR.'market-name-save.php';
    }

    // Manage : Monthly Headcount
    public function monthlyHeadcountAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'monthly-headcount'.DIRECTORY_SEPARATOR.'monthly-headcount.php';
    }

    public function monthlyHeadcountEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'monthly-headcount'.DIRECTORY_SEPARATOR.'monthly-headcount-edit.php';
    }

    public function monthlyHeadcountCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'monthly-headcount'.DIRECTORY_SEPARATOR.'monthly-headcount-create.php';
    }

    public function monthlyHeadcountSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'monthly-headcount'.DIRECTORY_SEPARATOR.'monthly-headcount-save.php';
    }

    // Manage : Monthly KPI Headcount
    public function monthlyHeadcountKpiAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'monthly-headcount-kpi'.DIRECTORY_SEPARATOR.'monthly-headcount-kpi.php';
    }

    public function monthlyHeadcountKpiEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'monthly-headcount-kpi'.DIRECTORY_SEPARATOR.'monthly-headcount-kpi-edit.php';
    }

    public function monthlyHeadcountKpiCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'monthly-headcount-kpi'.DIRECTORY_SEPARATOR.'monthly-headcount-kpi-create.php';
    }

    public function monthlyHeadcountKpiSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'monthly-headcount-kpi'.DIRECTORY_SEPARATOR.'monthly-headcount-kpi-save.php';
    }

    // Manage : Monthly Birthday PC
    public function monthlyBirthdayPcAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'monthly-birthday-pc'.DIRECTORY_SEPARATOR.'monthly-birthday-pc.php';
    }

    public function monthlyBirthdayPcEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'monthly-birthday-pc'.DIRECTORY_SEPARATOR.'monthly-birthday-pc-edit.php';
    }

    public function monthlyBirthdayPcCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'monthly-birthday-pc'.DIRECTORY_SEPARATOR.'monthly-birthday-pc-create.php';
    }

    public function monthlyBirthdayPcSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'monthly-birthday-pc'.DIRECTORY_SEPARATOR.'monthly-birthday-pc-save.php';
    }

    // Manage : Area Target
    public function oppoAreaTargetAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'oppo-area-target'.DIRECTORY_SEPARATOR.'oppo-area-target.php';
    }

    public function oppoAreaTargetSaveAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'oppo-area-target'.DIRECTORY_SEPARATOR.'oppo-area-target-save.php';
    }

    // Add check store target
      public function checkTargetAction(){
        require_once 'manage'.DIRECTORY_SEPARATOR.'check-target'.DIRECTORY_SEPARATOR.'check-target.php';
    }

    // Manaeg : Sale Target
    public function oppoSaleTargetAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'oppo-sale-target'.DIRECTORY_SEPARATOR.'oppo-sale-target.php';
    }
    public function oppoSaleTargetEditAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'oppo-sale-target'.DIRECTORY_SEPARATOR.'oppo-sale-target-edit.php';
    }
    public function oppoSaleTargetSaveAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'oppo-sale-target'.DIRECTORY_SEPARATOR.'oppo-sale-target-save.php';
    }
    public function oppoSaleTargetPrintAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'oppo-sale-target'.DIRECTORY_SEPARATOR.'oppo-sale-target-print.php';
    }
    public function oppoSaleTargetResetAreaAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'oppo-sale-target'.DIRECTORY_SEPARATOR.'oppo-sale-target-reset-area.php';
    }
    public function oppoSaleTargetResetSaleAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'oppo-sale-target'.DIRECTORY_SEPARATOR.'oppo-sale-target-reset-sale.php';
    }

    // Manage : Store Target
    public function oppoPcTargetAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'oppo-pc-target'.DIRECTORY_SEPARATOR.'oppo-pc-target.php';
    }
    public function oppoPcTargetSaveAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'oppo-pc-target'.DIRECTORY_SEPARATOR.'oppo-pc-target-save.php';
    }

    // Manage : PC Target
    public function oppoIndividualTargetAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'oppo-individual-target'.DIRECTORY_SEPARATOR.'oppo-individual-target.php';
    }
    public function oppoIndividualTargetSaveAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'oppo-individual-target'.DIRECTORY_SEPARATOR.'oppo-individual-target-save.php';
    }

    // Manage : Sales Achievement 
    public function salesAchievementAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sales-achievement'.DIRECTORY_SEPARATOR.'sales-achievement.php';
    }

    // Manage : Store Price Target
    public function storePriceTargetAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-price-target'.DIRECTORY_SEPARATOR.'store-price-target.php';
    }
    public function storePriceTargetSaveAction() {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-price-target'.DIRECTORY_SEPARATOR.'store-price-target-save.php';
    }

    // ASM target Index
    public function asmTargetAction()
    {
        require_once 'manage' . DIRECTORY_SEPARATOR. 'asm-target' .DIRECTORY_SEPARATOR. 'asm-target.php';
    }

    //Save ASM Target
    public function asmTargetSaveAction()
    {
        require_once 'manage' . DIRECTORY_SEPARATOR. 'asm-target' .DIRECTORY_SEPARATOR. 'asm-target-save.php';
    }

     //pcm target index Action
    public function pcmTargetAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR .'pcm-target'.DIRECTORY_SEPARATOR.'pcm-target.php';
    }

    //Save Pcm Target
    public function pcmTargetSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'pcm-target'.DIRECTORY_SEPARATOR.'pcm-target-save.php';
    }

     // mkt target Index
    public function mktTargetAction()
    {
        require_once 'manage' . DIRECTORY_SEPARATOR. 'mkt-target' .DIRECTORY_SEPARATOR. 'mkt-target.php';
    }

    //Save Mkt Target
    public function mktTargetSaveAction()
    {
        require_once 'manage' . DIRECTORY_SEPARATOR. 'mkt-target' .DIRECTORY_SEPARATOR. 'mkt-target-save.php';
    }

    // Manage : BS Area Control
    public function bsAreaControlAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'bs-area-control'.DIRECTORY_SEPARATOR.'bs-area-control.php';
    }

    public function bsAreaControlEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'bs-area-control'.DIRECTORY_SEPARATOR.'bs-area-control-edit.php';
    }

    public function bsAreaControlSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'bs-area-control'.DIRECTORY_SEPARATOR.'bs-area-control-save.php';
    }

    public function bsAreaControlShortReportAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'bs-area-control'.DIRECTORY_SEPARATOR.'bs-area-control-short-report.php';
    }

    // Manage : Store Control
    public function storeControlAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-control'.DIRECTORY_SEPARATOR.'store-control.php';
    }

    public function storeControlEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-control'.DIRECTORY_SEPARATOR.'store-control-edit.php';
    }

    public function storeControlSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-control'.DIRECTORY_SEPARATOR.'store-control-save.php';
    }

    public function storeControlShortReportAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-control'.DIRECTORY_SEPARATOR.'store-control-short-report.php';
    }

    public function storeControlCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-control'.DIRECTORY_SEPARATOR.'store-control-create.php';
    }

    public function storeControlCreateSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-control'.DIRECTORY_SEPARATOR.'store-control-create-save.php';
    }

    public function storeControlStoreEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-control'.DIRECTORY_SEPARATOR.'store-control-store-edit.php';
    }

    public function storeControlStoreSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-control'.DIRECTORY_SEPARATOR.'store-control-store-save.php';
    }

    public function storeControlDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-control'.DIRECTORY_SEPARATOR.'store-control-del.php';
    }

    // Store Focus List 
    public function storeFocusListAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-focus-list'.DIRECTORY_SEPARATOR.'store-focus-list.php';
    }
    public function storeFocusListCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-focus-list'.DIRECTORY_SEPARATOR.'store-focus-list-create.php';
    }
    public function storeFocusListCreateSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-focus-list'.DIRECTORY_SEPARATOR.'store-focus-list-create-save.php';
    }
    public function storeFocusListDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'store-focus-list'.DIRECTORY_SEPARATOR.'store-focus-list-del.php';
    }

    // Competitor Product
    public function competitorProductAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'competitor-product'.DIRECTORY_SEPARATOR.'competitor-product.php';
    }
    public function competitorProductCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'competitor-product'.DIRECTORY_SEPARATOR.'competitor-product-create.php';
    }
    public function competitorProductCreateSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'competitor-product'.DIRECTORY_SEPARATOR.'competitor-product-create-save.php';
    }


    // Penalty Charge
    public function penaltyChargeAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'penalty-charge'.DIRECTORY_SEPARATOR.'penalty-charge.php';
    }
    public function penaltyChargeCreateStoreAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'penalty-charge'.DIRECTORY_SEPARATOR.'penalty-charge-create-store.php';
    }
    public function penaltyChargeCreateStoreSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'penalty-charge'.DIRECTORY_SEPARATOR.'penalty-charge-create-store-save.php';
    }
    public function penaltyChargeCreateStaffAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'penalty-charge'.DIRECTORY_SEPARATOR.'penalty-charge-create-staff.php';
    }
    public function penaltyChargeCreateStaffSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'penalty-charge'.DIRECTORY_SEPARATOR.'penalty-charge-create-staff-save.php';
    }
    public function penaltyChargeStoreDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'penalty-charge'.DIRECTORY_SEPARATOR.'penalty-charge-store-del.php';
    }
    public function penaltyChargeStaffDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'penalty-charge'.DIRECTORY_SEPARATOR.'penalty-charge-staff-del.php';
    }

    // PC Un-Punish
    public function pcUnpunishAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'pc-unpunish'.DIRECTORY_SEPARATOR.'pc-unpunish.php';
    }
    public function pcUnpunishCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'pc-unpunish'.DIRECTORY_SEPARATOR.'pc-unpunish-create.php';
    }
    public function pcUnpunishCreateSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'pc-unpunish'.DIRECTORY_SEPARATOR.'pc-unpunish-create-save.php';
    }
    public function pcUnpunishDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'pc-unpunish'.DIRECTORY_SEPARATOR.'pc-unpunish-del.php';
    }

    // Bypass IMEI
    public function bypassImeiAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'bypass-imei'.DIRECTORY_SEPARATOR.'bypass-imei.php';
    }
    public function bypassImeiCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'bypass-imei'.DIRECTORY_SEPARATOR.'bypass-imei-create.php';
    }
    public function bypassImeiCreateSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'bypass-imei'.DIRECTORY_SEPARATOR.'bypass-imei-create-save.php';
    }
    public function bypassImeiDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'bypass-imei'.DIRECTORY_SEPARATOR.'bypass-imei-del.php';
    }

    // Menu 
    public function menuAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'menu'.DIRECTORY_SEPARATOR.'list.php';
    }

    public function menuCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'menu'.DIRECTORY_SEPARATOR.'create.php';
    }

    public function menuSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'menu'.DIRECTORY_SEPARATOR.'save.php';
    }

    public function menuDeleteAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'menu'.DIRECTORY_SEPARATOR.'delete.php';
    }

    public function eventChecklistAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'event-checklist'.DIRECTORY_SEPARATOR.'event-checklist.php';
    }

    public function eventChecklistEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'event-checklist'.DIRECTORY_SEPARATOR.'event-checklist-edit.php';
    }

    public function cacheAction(){
        $del = $this->getRequest()->getParam('del');

        if ($del) {

            $cache_folder = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;

            try {
                foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cache_folder, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                    if ($path->getFilename() == 'zend_cache---server_notifis_cache' 
                        || $path->getFilename() == 'zend_cache---internal-metadatas---server_notifis_cache') 
                        continue;
                    
                    $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
                }
            } catch (Exception $e){}

        }

        $back_url = $this->getRequest()->getServer('HTTP_REFERER');

        $this->_redirect( ( $back_url ? $back_url : HOST ) );
    }

    public function exceptionCaseAction()
    {
        $page = $this->getRequest()->getParam('page', 1);
        $limit = LIMITATION;
        $total = 0;

        $params = array();

        $QModel = new Application_Model_ExceptionCase();
        $exceptions = $QModel->fetchPagination($page, $limit, $total, $params);

        $this->view->exceptions = $exceptions;

        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST.'manage/exception-case/'.( $params ? '?'.http_build_query($params).'&' : '?' );
        $this->view->offset = $limit*($page-1);

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages = $messages;

        $this->_helper->viewRenderer->setRender('exception-case/index');
    }

    public function exceptionCaseCreateAction(){

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $QModel = new Application_Model_ExceptionCase();
            $exceptionRowset = $QModel->find($id);
            $exception_case = $exceptionRowset->current();

            $this->view->exception_case = $exception_case;

            switch ($exception_case->name) {
                case 'SALES_EXCEPTION':
                    include_once 'manage'.DIRECTORY_SEPARATOR.'exception-case'.DIRECTORY_SEPARATOR.'sales.php';
                    break;

                case 'ASM_EXCEPTION':
                    include_once 'manage'.DIRECTORY_SEPARATOR.'exception-case'.DIRECTORY_SEPARATOR.'asm.php';
                    break;

                default:
                    include_once 'manage'.DIRECTORY_SEPARATOR.'exception-case'.DIRECTORY_SEPARATOR.'default.php';
            }
        }

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        //back url
        $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

        $this->_helper->viewRenderer->setRender('exception-case/create');
    }

    public function exceptionCaseSaveAction(){

        if ($this->getRequest()->getMethod() == 'POST'){
            $name = $this->getRequest()->getParam('name');

            switch ($name){
                case 'SALES_EXCEPTION':
                    include_once 'manage'.DIRECTORY_SEPARATOR.'exception-case'.DIRECTORY_SEPARATOR.'sales-save.php';
                    break;

                case 'ASM_EXCEPTION':
                    include_once 'manage'.DIRECTORY_SEPARATOR.'exception-case'.DIRECTORY_SEPARATOR.'asm-save.php';
                    break;

                default:
                    include_once 'manage'.DIRECTORY_SEPARATOR.'exception-case'.DIRECTORY_SEPARATOR.'default-save.php';
            }


        } else

            $this->_redirect( HOST.'manage/exception-case' );
    }

    public function exceptionCaseDelAction(){
        $id = $this->getRequest()->getParam('id');

        $QModel = new Application_Model_ExceptionCase();
        $where = $QModel->getAdapter()->quoteInto('id = ?', $id);
        $QModel->delete($where);

        //remove cache
        $cache = Zend_Registry::get('cache');
        $cache->remove('exception_case_cache');

        $this->_redirect('/manage/exception-case');
    }

    /* SHIFT */
    public function shiftAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'shift'.DIRECTORY_SEPARATOR.'shift.php';

    }

    public function shiftCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'shift'.DIRECTORY_SEPARATOR.'shift-create.php';

    }

    public function shiftSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'shift'.DIRECTORY_SEPARATOR.'shift-save.php';

    }

    public function shiftDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'shift'.DIRECTORY_SEPARATOR.'shift-del.php';

    }

    public function asmAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'asm'.DIRECTORY_SEPARATOR.'asm.php';
    }

    public function asmEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'asm'.DIRECTORY_SEPARATOR.'asm-edit.php';
    }

    public function asmSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'asm'.DIRECTORY_SEPARATOR.'asm-save.php';
    }

    public function salesAdminAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sales-admin'.DIRECTORY_SEPARATOR.'sales-admin.php';
    }

    public function salesAdminEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sales-admin'.DIRECTORY_SEPARATOR.'sales-admin-edit.php';
    }

    public function salesAdminSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sales-admin'.DIRECTORY_SEPARATOR.'sales-admin-save.php';
    }

    public function asmStandbyAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'asm'.DIRECTORY_SEPARATOR.'standby.php';
    }

    public function asmStandbyEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'asm'.DIRECTORY_SEPARATOR.'standby-edit.php';
    }

    public function asmStandbySaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'asm'.DIRECTORY_SEPARATOR.'standby-save.php';
    }

    public function leaderAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'leader'.DIRECTORY_SEPARATOR.'list.php';
    }

    public function leaderViewAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'leader'.DIRECTORY_SEPARATOR.'view.php';
    }

    public function leaderSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'leader'.DIRECTORY_SEPARATOR.'save.php';
    }
    
    public function salesPgAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sales-pg'.DIRECTORY_SEPARATOR.'list.php';
    }

    public function salesPgViewAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sales-pg'.DIRECTORY_SEPARATOR.'view.php';
    }

    public function salesPgSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'sales-pg'.DIRECTORY_SEPARATOR.'save.php';
    }
    
    public function informAction()
    {
         require_once 'manage'.DIRECTORY_SEPARATOR.'inform'.DIRECTORY_SEPARATOR.'index.php';
    }
    
    public function informUploadAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'inform'.DIRECTORY_SEPARATOR.'inform-upload.php';
    }
    
    public function informUploadSaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'inform'.DIRECTORY_SEPARATOR.'inform-upload-save.php';
    }
    
    public function informCreateAction()
    {
         require_once 'manage'.DIRECTORY_SEPARATOR.'inform'.DIRECTORY_SEPARATOR.'inform-create.php';
    }
    
    public function informViewAction()
    {
         require_once 'manage'.DIRECTORY_SEPARATOR.'inform'.DIRECTORY_SEPARATOR.'inform-view.php';
    }
    
    public function informSaveAction()
    {
         require_once 'manage'.DIRECTORY_SEPARATOR.'inform'.DIRECTORY_SEPARATOR.'inform-save.php';
    }
    
    public function informDelAction()
    {
         require_once 'manage'.DIRECTORY_SEPARATOR.'inform'.DIRECTORY_SEPARATOR.'inform-del.php';
    }
    
    public function informCategoryAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'inform'.DIRECTORY_SEPARATOR.'inform-category.php';
    }
    
    public function informCategoryCreateAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'inform'.DIRECTORY_SEPARATOR.'inform-category-create.php';
    }
    
    public function informCategoryEditAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'inform'.DIRECTORY_SEPARATOR.'inform-category-edit.php';
    }
    
    public function informCategorySaveAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'inform'.DIRECTORY_SEPARATOR.'inform-category-save.php';
    }
    
     public function informCategoryDelAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'inform'.DIRECTORY_SEPARATOR.'inform-category-del.php';
    }


     /**************************** Grand Area By PungPond **************************************/
     public function grandAreaAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'grand-area'.DIRECTORY_SEPARATOR.'grand-area.php';
    }
     public function editGrandAreaAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'grand-area'.DIRECTORY_SEPARATOR.'edit.php';
    }
     public function createGrandAreaAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'grand-area'.DIRECTORY_SEPARATOR.'create.php';
    }
     public function saveGrandAreaAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'grand-area'.DIRECTORY_SEPARATOR.'save.php';
    }
    public function addRmAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'grand-area'.DIRECTORY_SEPARATOR.'add-rm.php';
    }
    public function saveRmAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'grand-area'.DIRECTORY_SEPARATOR.'save-rm.php';
    }
    public function grandDeleteAction()
    {
        require_once 'manage'.DIRECTORY_SEPARATOR.'grand-area'.DIRECTORY_SEPARATOR.'grand-delete.php';
    }
     /************************* END Grand Area **************************************/
    private function generate_list($group_menus) {
        return $this->ul(0, '', $group_menus);
    }
    
    function ul($parent = 0, $attr = '', $group_menus = null) {
        static $i = 1;
        $indent = str_repeat("\t\t", $i);
        if (isset($this->data)) {
            if ($attr) {
                $attr = ' ' . $attr;
            }
            $html = "\n$indent";
            $html .= "<ul$attr>";
            $i++;
            foreach ($this->data as $row) {
                $html .= "\n\t$indent";
                $html .= '<li>';
                $html .= '<input value="'.$row['id'].'" '.( ($group_menus and in_array($row['id'] , $group_menus)) ? 'checked' : '' ).' type="checkbox" id="menus_'.$row['id'].'" name="menus[]"><label for="menus_'.$row['id'].'">'.$row['label'].'</label>';

                $html .= '</li>';
            }
            $html .= "\n$indent</ul>";
            return $html;
        } else {
            return false;
        }
    }

    private function add_row($id, $label) {
        $this->data[] = array('id' => $id, 'label' => $label);
    }

    public function loadMarketNameAction() {

        $area_id = $this->getRequest()->getParam('area_id');
        $market_type_id = $this->getRequest()->getParam('market_type_id'); 

        if ( !is_array($area_id) ) { $area_id = json_decode($area_id, true); }

        $QMarketName = new Application_Model_MarketName();
        
        if ( isset($market_type_id) && !empty($market_type_id) ) {
            $where[] = $QMarketName->getAdapter()->quoteInto('market_type_id IN (?)', $market_type_id);
        }

        if ( isset($area_id) && !empty($area_id) ) {
            $where[] = $QMarketName->getAdapter()->quoteInto('area_id IN (?)', $area_id);
        }

        echo json_encode($QMarketName->fetchAll($where, 'name')->toArray());
        exit;
    }

    public function editMarketNameAction() {

        $back_url = $this->getRequest()->getParam('back_url');
        $this->view->back_url = $back_url;

        //$id = $this->getRequest()->getParam('market_name_id');
        $this->view->market_name_id = $_POST['market_name_id'];
        $this->view->market_name    = $_POST['market_name'];
        $this->view->market_type_id = $_POST['market_type_id'];

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        $messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages_success = $messages_success;

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setRender('store/edit-market-name');
        //back url

    }

    public function saveMarketNameAction() {

        $this->_helper->layout->disableLayout();

        $QMarketName    = new Application_Model_MarketName();
        $userStorage    = Zend_Auth::getInstance()->getStorage()->read();
        $market_name    = $_POST['market_name'];
        $market_type_id = $_POST['market_type_id'];

        if ($_POST['market_name_id']) {
            $old_id = $_POST['market_name_id'];
            $where = $QMarketName->getAdapter()->quoteInto('id = ?', $old_id);

            $data = array(
                'name'       => $market_name,
                'updated_by' => $userStorage->id,
                'updated_at' => date('Y-m-d H:i:s')
            );

            $result = $QMarketName->update($data, $where);
        } else {

            $data = array(
                'name'              => $market_name,
                'market_type_id'    => $market_type_id,
                'created_by'        => $userStorage->id,
                'created_at'        => date('Y-m-d H:i:s')
            );

            $result = $QMarketName->insert($data);
        }
        
        //if ($result) {  exit('-1'); } else {  exit('-2'); }
        exit();
    }

    private function _exportMarketName($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'NO.',
            'Area',
            'Market Type',
            'Market Name ID',
            'Market Name',
            'Number of Store'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
        for ($i=0;$i<count($data);$i++) {

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['mt_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['mn_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['mn_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store']);
            
            //$PHPExcel->getActiveSheet()->getStyle('I'.$index)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

            $index++;
        }
        
        $filename = 'Market_Name_list_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    public function _exportOverViewTarget($result, $params) {

        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);

        $filename = 'Report_OverViewTarget_'.date('d/m/Y');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $heads = array(
            'No.',
            'Area',
            'Area Target',
            'Leader Code',
            'Leader Name',
            'Group',
            'Sale Target',
            'Store ID',
            'Store Name',
            'Store Type',
            'Market Type',
            'Market Name',
            'Number of PC',
            'Store Target',
            'Shop Sellout',
            'Store Achieve',
        );

        fputcsv($output, $heads);

        $no = 1;
        $date2 = new DateTime();

        $QGrandAreaRm = new Application_Model_GrandAreaRm();
        $QStoreStaffLog = new Application_Model_StoreStaffLog();
        $QStoreStaff = new Application_Model_StoreStaff();

        // Set Grand Area of BKK
        $grand_e1 = array(81,82,83,110,111,112);
        $grand_e2 = array(85,86,87,115,88,89,116,117);
        $grand_e3 = array(90,91,92,93,113);
        $grand_e4 = array(94,95,96);
        $grand_e5 = array(97,109);
        $grand_w1 = array(98,99,100,101,102,114);
        $grand_w2 = array(103,104,105);
        $grand_w3 = array(106,107,108);

        $grand_area = "";

        foreach ($result as $data) {

            $result_ga = $QGrandAreaRm->getGrandAreaByArea($data['area_id']);
            $achieve = ( $data['sellout'] / $data['opt_target'] ) * 100;

            $result_pc = $QStoreStaff->getPC($data['st_id'], 0);
            $result_pc_sb = $QStoreStaff->getPC($data['st_id'], 1);

            // All PC in Store
            $cnt_store_pc = count($result_pc) + count($result_pc_sb);

            if ($cnt_store_pc > 0) { 
                
                $pc_target = round($data['opt_target'] / $cnt_store_pc); 
                $pc_hero_target = round($data['opt_target_hero'] / $cnt_store_pc); 

            }

            $where_ssl = array();
            $where_ssl[] = $QStoreStaffLog->getAdapter()->quoteInto('store_id = ?', $data['st_id']);
            $where_ssl[] = $QStoreStaffLog->getAdapter()->quoteInto('staff_id = ?', $data['pc_id']);
            $where_ssl[] = $QStoreStaffLog->getAdapter()->quoteInto('is_leader = ?', 0);
            $where_ssl[] = $QStoreStaffLog->getAdapter()->quoteInto('released_at IS NULL', 1);
            $result_ssl = $QStoreStaffLog->fetchRow($where_ssl);


            $work_day = "No";
            if( isset($result_ssl['joined_at']) ) {
                $now = time();
                $cal_work = ($now - $result_ssl['joined_at'])/(24*60*60);
                if ($cal_work <= 30) { $work_day = "Yes"; }
            }

            $row = array();
            $row[] = $no;
            $row[] = $data['area_name'];
            $row[] = $data['oat_target'];

            $row[] = $data['sale_code'];
            $row[] = $data['sale_name'];
            $row[] = $data['sale_group'];
            $row[] = $data['ost_target'];

            $row[] = $data['st_id'];
            $row[] = $data['st_name'];
            $row[] = $data['st_type'];
            $row[] = $data['mt_name'];
            $row[] = $data['mn_name'];

            $row[] = count($result_pc);
            $row[] = $data['opt_target'];
            $row[] = $data['sellout'];
            $row[] = number_format($achieve,2)."%";

            fputcsv($output, $row);
            $no++;
        }
        exit;

    }

    private function _exportareatarget($data){

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area',
            'RD Code',
            'RD Name',
            'Target',
            'Hero Target',
            'Target Sellout',
            'Target Activated',
            'Hero Sellout',
            'Hero Activated',
            'Achieve Hero Sellout',
            'Achieve Hero Activated',
            'Achieve Target Sellout',
            'Achieve Target Activated',
            'Target From DateTime',
            'Target To DateTime',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
        $cnt = 1;
        for ($i=0;$i<count($data); $i++) {
            $achieve_target_sellout = $data[$i]['sellout'] / $data[$i]['area_target'] * 100;
            $achieve_target_activate = $data[$i]['activate'] / $data[$i]['area_target'] * 100;
            $achieve_hero_sellout = $data[$i]['hero_product_sellout'] / $data[$i]['target_hero'] * 100;
            $achieve_hero_activate = $data[$i]['hero_product_activate'] / $data[$i]['target_hero'] * 100;



            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['rd_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['rd_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_target']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['target_hero']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['activate']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_product_sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_product_activate']);
            $sheet->setCellValue($alpha++.$index, number_format($achieve_hero_sellout,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_hero_activate,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_target_sellout,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_target_activate,2)."%");
            $sheet->setCellValue($alpha++.$index, $data[$i]['from']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['to']);

            $index++;
            $cnt++;
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'Export_RGM_Target_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

// Export Sale Target
    private function _exportSaleSetTarget($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Area',
            'Provience',
            'Sale Code',
            'Sale Name',
            'Sub Area',
            'Target',
            'Hero Target',
            'Target Sellout',
            'Target Activated',
            'Hero Sellout',
            'Hero Activated',
            'Achieve Hero Sellout',
            'Achieve Hero Activated',
            'Achieve Target Sellout',
            'Achieve Target Activated',
            'Target From DateTime',
            'Target To DateTime',
            'Approve By',
            'Approve At',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;
        $no = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QStaff = New Application_Model_Staff();
        $staff = $QStaff->get_cache();

        $index = 2;
        for ($i=0;$i<count($data); $i++) {


            $achieve = ( $data[$i]['sellout'] / $data[$i]['sale_target'] ) * 100;
            $achieve_activate = ( $data[$i]['activate'] / $data[$i]['sale_target'] ) * 100;
            $achieve_hero = ( $data[$i]['hero_product_sellout'] / $data[$i]['target_hero'] ) * 100;
            $achieve_hero_activate = ( $data[$i]['hero_product_activate'] / $data[$i]['target_hero'] ) * 100;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $no);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['provience']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sub_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_target']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['target_hero']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['activate']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_product_sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_product_activate']);
            $sheet->setCellValue($alpha++.$index, number_format($achieve_hero,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_hero_activate,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_activate,2)."%");
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_from']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_to']);
            $sheet->setCellValue($alpha++.$index, $staff [$data[$i]['approve_by']]);
            $sheet->setCellValue($alpha++.$index, $data[$i]['approve_at']);

            $index++;
            $no++;
        }
        
        $filename = 'Export_OPPO_Sales_Target_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

// Export PC Target
    public function _exportPCTarget($result, $params) {

        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);

        $filename = 'PC_Target_'.date('d/m/Y');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $heads = array(
            'No.',
            'Area',
            'Procince',
            'Store ID',
            'Store Name',
            'PC Code',
            'PC Name',
            'Group',
            'Target',
            'Hero Target',
            'Target Sellout',
            'Target Activated',
            'Hero Sellout',
            'Hero Activated',
            'Achieve Hero Sellout',
            'Achieve Hero Activated',
            'Achieve Target Sellout',
            'Achieve Activated Sellout',
        );

        fputcsv($output, $heads);

        $date2 = new DateTime();

        $QGrandAreaRm = new Application_Model_GrandAreaRm();
        $QStoreStaffLog = new Application_Model_StoreStaffLog();

        $QArea = new Application_Model_Area();
        $area = $QArea->get_cache();

        $no = 1;

        foreach ($result as $data) {


            $result_ga = $QGrandAreaRm->getGrandAreaByArea($data['area_id']);
            $achieve = ( $data['pc_sellout'] / $data['pc_target'] ) * 100;
            $achieve_activate = ( $data['activate'] / $data['pc_target'] ) * 100;
            $achieve_hero_sellout = ( $data['hero_product_sellout'] / $data['target_hero'] ) * 100;
            $achieve_hero_sellout_activate = ( $data['hero_product_activate'] / $data['target_hero'] ) * 100;

            $where_ssl = array();
            $where_ssl[] = $QStoreStaffLog->getAdapter()->quoteInto('store_id = ?', $data['st_id']);
            $where_ssl[] = $QStoreStaffLog->getAdapter()->quoteInto('staff_id = ?', $data['pc_id']);
            $where_ssl[] = $QStoreStaffLog->getAdapter()->quoteInto('is_leader = ?', 0);
            $where_ssl[] = $QStoreStaffLog->getAdapter()->quoteInto('released_at IS NULL', 1);
            $result_ssl = $QStoreStaffLog->fetchRow($where_ssl);

            $work_day = "No";
            if( isset($result_ssl['joined_at']) ) {
                $now = time();
                $cal_work = ($now - $result_ssl['joined_at'])/(24*60*60);
                if ($cal_work <= 30) { $work_day = "Yes"; }
            }

            $row = array();
            $row[] = $no;
            $row[] = $area[$data['area_id']];
            $row[] = $data['province'];

            $row[] = $data['st_id'];
            $row[] = $data['st_name'];

            $row[] = $data['pc_code'];
            $row[] = $data['pc_name'];
            $row[] = $data['pc_group'];
            
            $row[] = $data['pc_target'];
            $row[] = $data['target_hero'];

            $row[] = $data['pc_sellout'];
            $row[] = $data['activate'];
            $row[] = $data['hero_product_sellout'];
            $row[] = $data['hero_product_activate'];

            $row[] = number_format($achieve_hero_sellout,2)."%";
            $row[] = number_format($achieve_hero_sellout_activate,2)."%";
            $row[] = number_format($achieve,2)."%";
            $row[] = number_format($achieve_activate,2)."%";

            fputcsv($output, $row);
            $no++;
        }
        exit;

    }

    //export Trainer Targte
       private function _ExportPCMTargetSellOut($data){

        
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area',
            'PCM Code',
            'PCM Name',
            'PC Count',
            'Target',
            'Hero Target',
            'Target Sellout',
            'Target Sellout Activated',
            'Hero Sellout',
            'Hero Sellout Activated',

            'Achieve Target Sellout',
            'Achieve Target Sellout Activated',
            'Achieve Hero Sellout',
            'Achieve Hero Sellout Activated',
            // 'Achieve PC Target Sellout ',
            // 'Achieve PC Hero Sellout',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
        $cnt = 1;
        for ($i=0;$i<count($data); $i++) {
            $achieve_pcm_target_sellout = $data[$i]['sellout'] / $data[$i]['target'] * 100;
            $achieve_pcm_target_activate = $data[$i]['activate'] / $data[$i]['target'] * 100;
            $achieve_pcm_hero_sellout = $data[$i]['hero_product'] / $data[$i]['target_hero'] * 100;
            $achieve_pcm_hero_activate = $data[$i]['hero_product_activate'] / $data[$i]['target_hero'] * 100;

            $achieve_pcm_pc_target_sellout = $data[$i]['sellout'] / $data[$i]['count'];
            $achieve_pcm_pc_hero_sellout = $data[$i]['hero_product'] / $data[$i]['count'];



            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['count']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['target']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['target_hero']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['activate']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_product']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_product_activate']);

            $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_target_sellout,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_target_activate,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_hero_sellout,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_hero_activate,2)."%");

            // $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_pc_target_sellout)."%");
            // $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_pc_hero_sellout)."%");

            $index++;
            $cnt++;
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'Export_Trainer_Target_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

        //export MKT Targte
       private function _ExportMKTTargetSellOut($data){

        
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area',
            'MKT Code',
            'MKT Name',
            'Target',
            'Hero Target',

            'Target Sellout',
            'Target Sellout Activated',
            'Hero Sellout',
            'Hero Sellout Activated',
            'Achieve Target Sellout',
            'Achieve Target Sellout Activated',
            'Achieve Hero Sellout',
            'Achieve Hero Sellout Activated',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
        $cnt = 1;
        for ($i=0;$i<count($data); $i++) {
            $achieve_pcm_target_sellout = $data[$i]['sellout'] / $data[$i]['target'] * 100;
            $achieve_pcm_target_activate = $data[$i]['activate'] / $data[$i]['target'] * 100;
            $achieve_pcm_hero_sellout = $data[$i]['hero_product'] / $data[$i]['target_hero'] * 100;
            $achieve_pcm_hero_activate = $data[$i]['hero_product_activate'] / $data[$i]['target_hero'] * 100;

            $achieve_pcm_pc_target_sellout = $data[$i]['sellout'] / $data[$i]['count'];
            $achieve_pcm_pc_hero_sellout = $data[$i]['hero_product'] / $data[$i]['count'];

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['target']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['target_hero']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['activate']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_product']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_product_activate']);

            $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_target_sellout,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_target_activate,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_hero_sellout,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_hero_activate,2)."%");


            $index++;
            $cnt++;
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'MKT_Target_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    //export ASM Targte
    private function _ExportASMTargetSellOut($data){


        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area',
            'ASM Code',
            'ASM Name',
            'Target',
            'Hero Target',

            'Target Sellout',
            'Target Sellout Activated',
            'Hero Sellout',
            'Hero Sellout Activated',
            'Achieve Target Sellout',
            'Achieve Target Sellout Activated',
            'Achieve Hero Sellout',
            'Achieve Hero Sellout Activated',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
        $cnt = 1;
        for ($i=0;$i<count($data); $i++) {
            $achieve_pcm_target_sellout = $data[$i]['sellout'] / $data[$i]['target'] * 100;
            $achieve_pcm_target_activate = $data[$i]['activate'] / $data[$i]['target'] * 100;
            $achieve_pcm_hero_sellout = $data[$i]['hero_product'] / $data[$i]['target_hero'] * 100;
            $achieve_pcm_hero_activate = $data[$i]['hero_product_activate'] / $data[$i]['target_hero'] * 100;

            $achieve_pcm_pc_target_sellout = $data[$i]['sellout'] / $data[$i]['count'];
            $achieve_pcm_pc_hero_sellout = $data[$i]['hero_product'] / $data[$i]['count'];

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['target']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['target_hero']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['activate']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_product']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_product_activate']);

            $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_target_sellout,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_target_activate,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_hero_sellout,2)."%");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_pcm_hero_activate,2)."%");


            $index++;
            $cnt++;
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'Export_RM_Target_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    private function _exportDailySelloutByPC($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $tmp_from = explode('/', $params['from']);
        $tmp_to = explode('/', $params['to']);

        $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
        $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];

        $day_loop = (( strtotime($to) - strtotime($from) ) / (24*60*60)) + 1;

        $head_01 = array(
            'No.',
            'Grand Area',
            'Area',
            'Staff Code',
            'Staff Name',
            'Group',
            'Joined At',
            'Off Date',
            'Store ID',
            'Store Name',
            'Store Type',
        );

        for ($i=0;$i<$day_loop;$i++) {
            $day = date('d M Y', strtotime("+".$i." Day" , strtotime($from)));
            array_push($head_01, $day);
        }
        
        array_push($head_01, 'F9');
        array_push($head_01, 'Others');
        array_push($head_01, 'Total Sellout');
        array_push($head_01, 'Work Day [Month]');

        // Check for Report PC Daily Sellout by Shop
        if (isset($params['by_shop']) && $params['by_shop']) {
            $head_02 = array('Staff Code','Staff Name','Group','Joined At','Off Date','Work Day [Month]');
            $heads = array_diff($head_01,$head_02);
            $filename = 'PC_Daily_Sellout_by_Shop_'.date('Y-m-d');
        } else { 
            $heads = $head_01; 
            $filename = 'Daily_Sellout_by_PC_'.date('Y-m-d');
        }

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $link_style = array(
            'font' => array(
                'color' => ['rgb' => '0000FF'],
                'underline' => 'single',
            )
        );

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            // Check for Report PC Daily Sellout by Shop
            if (isset($params['by_shop']) && $params['by_shop']) {

                $alpha    = 'A';
                $sheet->setCellValue($alpha++.$index, $cnt);
                $sheet->setCellValue($alpha++.$index, $data[$i]['ga_name']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);

                for ($j=0;$j<$day_loop;$j++) {
                    $day = date('Y-m-d', strtotime("+".$j." Day" , strtotime($from)));
                    $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_'.$j]);
                }

                $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_focus_01']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_focus_others']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['total_sellout']);
                
            } else {

                $from_url = str_replace("/", "%2F", $params['from']);
                $to_url = str_replace("/", "%2F", $params['to']);
                
                $export_url = HOST."sales-report/timing-detail?staff_code=".$data[$i]['staff_code']."&from=".$from_url."&to=".$to_url."&export=5";

                $alpha    = 'A';
                $sheet->setCellValue($alpha++.$index, $cnt);
                $sheet->setCellValue($alpha++.$index, $data[$i]['ga_name']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['joined_at']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['off_date']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);

                for ($j=0;$j<$day_loop;$j++) {
                    $day = date('Y-m-d', strtotime("+".$j." Day" , strtotime($from)));
                    $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_'.$j]);
                }

                $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_focus_01']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_focus_others']);

                $sheet->setCellValue($alpha.$index, $data[$i]['total_sellout']);

                $sheet->getCell($alpha.$index)->getHyperlink()->setUrl($export_url);
                $sheet->getStyle($alpha.$index)->applyFromArray($link_style);

                $alpha++;

                $sheet->setCellValue($alpha++.$index, $data[$i]['month_diff']);

            }

            $index++;
        }

        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    public function loadStoreByAreaAction() {

        $area_id = $this->getRequest()->getParam('area_id');

        $db = Zend_Registry::get('db');

        $QStoreStaff = new Application_Model_StoreStaff();

        $get = array(
            'st_area'   => 'a.name',
            'st_id'     => 'st.id', 
            'st_name'   => 'st.name',
            'st_del'    => 'st.del',
            'st_type'   => 'o.org_name',
            'sale_code' => 's.code',
            'sale_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'sc_name'   => 'sc.name',
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'            , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'          , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'                   , array())
            ->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id'                  , array())
            ->joinLeft(array('scm'=> 'store_control_map'), 'st.id = scm.store_id'           , array())
            ->joinLeft(array('sc' => 'store_control')    , 'scm.store_control_id = sc.id'   , array())
            ->order(array('sc.name ASC', 'st.del ASC', 'a.name ASC', 'st.id ASC'));

        if ($area_id != 'null') { $select->where('a.id IN (?)', $area_id); }
        else { $select->where('1=0', 1); }

        $data = $db->fetchAll($select);

        $result = array();
        for ($i=0;$i<count($data);$i++) {

            $pc_list = $QStoreStaff->getPC($data[$i]['st_id']);

            $result[$i]['st_area']      = $data[$i]['st_area'];
            $result[$i]['st_id']        = $data[$i]['st_id'];
            $result[$i]['st_name']      = $data[$i]['st_name'];
            $result[$i]['st_del']       = $data[$i]['st_del'];
            $result[$i]['st_type']      = $data[$i]['st_type'];
            $result[$i]['sale_code']    = $data[$i]['sale_code'];
            $result[$i]['sale_name']    = $data[$i]['sale_name'];
            $result[$i]['sc_name']      = $data[$i]['sc_name'];

            $result[$i]['pc_list'] = "";
            $flag_pc = count($pc_list) - 1;
            for ($j=0;$j<count($pc_list);$j++) { 
                $result[$i]['pc_list'] = $result[$i]['pc_list']."[ ".$pc_list[$j]['pc_code']." ] ".$pc_list[$j]['pc_name']."<br/>";
                if ($j == $flag_pc) { $result[$i]['pc_list']."[ ".$pc_list[$j]['pc_code']." ] ".$pc_list[$j]['pc_name']; }
            }

        }

        // echo $select;
        echo json_encode($result);
        exit;
    }

    // Export Penalty Charge [By Staff]
    private function _exportPenaltyList($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area Name',
            'Staff Code',
            'Position',
            'Name',
            'Punish Type',
            'Price',
            'Remark',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle('A1:H1')->applyFromArray($style);

        $sum_price = 0;
        $index = 2;

        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['punish_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['price']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['pm_remark']);

            $sum_price = $sum_price + $data[$i]['price'];

            $index++;
        }

        // Last Row
        $alpha    = 'A';
        $sheet->setCellValue($alpha++.$index, 'Total');
        $sheet->setCellValue($alpha++.$index, '');
        $sheet->setCellValue($alpha++.$index, '');
        $sheet->setCellValue($alpha++.$index, '');
        $sheet->setCellValue($alpha++.$index, '');
        $sheet->setCellValue($alpha++.$index, '');
        $sheet->setCellValue($alpha++.$index, $sum_price);
        $sheet->setCellValue($alpha++.$index, '');

        $sheet->mergeCells('A'.$index.':F'.$index);
        $sheet->getStyle('A'.$index.':F'.$index)->applyFromArray($style);
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'PenaltyCharge_List_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Store Focus List
    public function _exportStoreFocusList($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area Name',
            'Store ID',
            'Store Name',
            'Store Type',
            'Status',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle('A1:F1')->applyFromArray($style);

        $sum_price = 0;
        $index = 2;

        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_status']);

            $index++;
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'StoreFocusList_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Monthly Headcount List
    public function _exportHeadcountList($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area Name',
            'RD',
            'Assistant',
            'ASM',
            'PCM Leader',
            'Sale',
            'PCM',
            'TMS',
            'Total',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle('A1:J1')->applyFromArray($style);

        $index = 2;

        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_rd']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_assist']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_asm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm_leader']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_tms']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_total']);

            $index++;
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'Monthly_Headcount_List_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Monthly Headcount Detials
    public function _exportHeadcountDetails($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area Name',
            'Staff Code',
            'Staff Name',
            'Staff Group',
            'Off Date',
            'Phone Number',
            'RD Name',
            'Month-Year',
            'Created By [Code]',
            'Created By [Name]',
            'Created By [Group]',
            'Created At',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle('A1:J1')->applyFromArray($style);

        $QStaff = new Application_Model_Staff();

        $index = 2;

        for ($i=0;$i<count($data); $i++) {

            $where = $QStaff->getAdapter()->quoteInto('code = ?', $data[$i]['staff_code']);
            $result_staff = $QStaff->fetchRow($where);

            $staff_off_date = '';
            if ( !empty($result_staff) ) { $staff_off_date = $result_staff['off_date']; } 

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++.$index, $staff_off_date);
            $sheet->setCellValue($alpha++.$index, '="'.$data[$i]['phone_number'].'"');
            $sheet->setCellValue($alpha++.$index, $data[$i]['rd_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['month_year']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['created_by_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['created_by_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['created_by_group']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['created_at']);

            $index++;
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'Monthly_Headcount_Details_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Monthly Headcount List for UPC Rate
    public function _exportHeadcountListUPC($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $heads_01 = array("สรุปค่าใช้จ่ายประจำเดือน ".$params['month_year']." [UPC]");

        $alpha = 'A';
        $index = 1;
        foreach ($heads_01 as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $heads_02 = array(
            'No. 序号',
            'Area 区域',
            'ชี่อ-นามสกุล ที่จะโอนเงิน 需要转账的名单',
            'ตำแหน่ง 职位',
            'RD',
            'Assistant 助理',
            'ASM',
            'PCM Leader',
            'Sale',
            'PCM',
            'TMS',
            'Total 总计',
            'งบค่าใช้จ่าย / คน 预算费/人',
            'สรุปยอดค่าใช้จ่าย 消费总计',
        );

        $alpha    = 'A';
        $index    = 2;
        foreach($heads_02 as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:O1');
        $sheet->getStyle("A1:O2")->applyFromArray($style);

        $QAsm = new Application_Model_Asm();

        $budget_staff = 9000;
        $budget_total = 0;
        $index = 3;

        $sum_rd = $sum_assist = $sum_asm = $sum_pcm_leader = $sum_sale = $sum_pcm = $sum_tms = $sum_total = 0;
        $sum_budget_total = 0;

        for ($i=0;$i<count($data); $i++) {

            $budget_total = ($data[$i]['cnt_rd'] + $data[$i]['cnt_assist'] + $data[$i]['cnt_asm'] + $data[$i]['cnt_pcm_leader'] + 
                $data[$i]['cnt_sale'] + $data[$i]['cnt_pcm'] + $data[$i]['cnt_tms']) * $budget_staff;

            $result_rd = $QAsm->getStaffByAreaID($data[$i]['area_id'], 1);

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);

            $sheet->setCellValue($alpha++.$index, $result_rd[0]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $result_rd[0]['group_name']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_rd']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_assist']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_asm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm_leader']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_tms']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_total']);

            $sheet->setCellValue($alpha++.$index, $budget_staff);
            $sheet->setCellValue($alpha++.$index, $budget_total);

            $sum_rd         = $sum_rd           + $data[$i]['cnt_rd'];
            $sum_assist     = $sum_assist       + $data[$i]['cnt_assist'];
            $sum_asm        = $sum_asm          + $data[$i]['cnt_asm'];
            $sum_pcm_leader = $sum_pcm_leader   + $data[$i]['cnt_pcm_leader'];
            $sum_sale       = $sum_sale         + $data[$i]['cnt_sale'];
            $sum_pcm        = $sum_pcm          + $data[$i]['cnt_pcm'];
            $sum_tms        = $sum_tms          + $data[$i]['cnt_tms'];
            $sum_total      = $sum_total        + $data[$i]['cnt_total'];

            $sum_budget_total = $sum_budget_total + $budget_total;

            $index++;
        }

        $alpha = 'A';
        $sheet->setCellValue($alpha++.$index, "Total 总计");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, "");

        $sheet->setCellValue($alpha++.$index, $sum_rd);
        $sheet->setCellValue($alpha++.$index, $sum_assist);
        $sheet->setCellValue($alpha++.$index, $sum_asm);
        $sheet->setCellValue($alpha++.$index, $sum_pcm_leader);
        $sheet->setCellValue($alpha++.$index, $sum_sale);
        $sheet->setCellValue($alpha++.$index, $sum_pcm);
        $sheet->setCellValue($alpha++.$index, $sum_tms);
        $sheet->setCellValue($alpha++.$index, $sum_total);
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, $sum_budget_total);

        $sheet->mergeCells("A".$index.":D".$index);
        $sheet->getStyle("A".$index.":D".$index)->applyFromArray($style);
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'Monthly_Headcount_List_UPC_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Monthly Headcount List for BKK Rate
    public function _exportHeadcountListBKK($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $heads_01 = array("สรุปค่าใช้จ่ายประจำเดือน ".$params['month_year']." [BKK]");

        $alpha = 'A';
        $index = 1;
        foreach ($heads_01 as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $heads_02 = array(
            'No. 序号',
            'Area 区域',
            'ชี่อ-นามสกุล ที่จะโอนเงิน 需要转账的名单',
            'ตำแหน่ง 职位',
            'RD',
            'ASM',
            'PCM Leader',
            'งบค่าใช้จ่าย / คน 预算费/人',
            'ค่าใช้จ่ายส่วนที่ 1 第一部分的费用',

            'Assistant 助理',
            'Sale',
            'PCM',
            'TMS',
            'งบค่าใช้จ่าย / คน 预算费/人',
            'ค่าใช้จ่ายส่วนที่ 2 第二部分的费用',

            'สรุปยอดค่าใช้จ่าย 消费总计',
            'ค่า Office',
            'Final Paid',
        );

        $alpha    = 'A';
        $index    = 2;
        foreach($heads_02 as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:R1');
        $sheet->getStyle("A1:R2")->applyFromArray($style);

        $QAsm = new Application_Model_Asm();

        $budget_staff_01 = 9000;
        $budget_staff_02 = 5000;
        $budget_total_01 = $budget_total_02 = 0;
        $index = 3;

        $sum_rd = $sum_assist = $sum_asm = $sum_pcm_leader = $sum_sale = $sum_pcm = $sum_tms = 0;
        $sum_budget_total_01 = $sum_budget_total_02 = 0;

        for ($i=0;$i<count($data); $i++) {

            $budget_total_01 = ($data[$i]['cnt_rd'] + $data[$i]['cnt_asm'] + $data[$i]['cnt_pcm_leader']) * $budget_staff_01;
            $budget_total_02 = ($data[$i]['cnt_assist'] + $data[$i]['cnt_sale'] + $data[$i]['cnt_pcm'] + $data[$i]['cnt_tms']) * $budget_staff_02;

            $result_rd = $QAsm->getStaffByAreaID($data[$i]['area_id'], 1);

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);

            $sheet->setCellValue($alpha++.$index, $result_rd[0]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $result_rd[0]['group_name']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_rd']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_asm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm_leader']);

            $sheet->setCellValue($alpha++.$index, $budget_staff_01);
            $sheet->setCellValue($alpha++.$index, $budget_total_01);

            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_assist']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_tms']);

            $sheet->setCellValue($alpha++.$index, $budget_staff_02);
            $sheet->setCellValue($alpha++.$index, $budget_total_02);
            $sheet->setCellValue($alpha++.$index, "=I".$index."+O".$index);
            $sheet->setCellValue($alpha++.$index, "");
            $sheet->setCellValue($alpha++.$index, "=P".$index."-Q".$index);

            $sum_rd         = $sum_rd           + $data[$i]['cnt_rd'];
            $sum_asm        = $sum_asm          + $data[$i]['cnt_asm'];
            $sum_pcm_leader = $sum_pcm_leader   + $data[$i]['cnt_pcm_leader'];

            $sum_assist     = $sum_assist       + $data[$i]['cnt_assist'];
            $sum_sale       = $sum_sale         + $data[$i]['cnt_sale'];
            $sum_pcm        = $sum_pcm          + $data[$i]['cnt_pcm'];
            $sum_tms        = $sum_tms          + $data[$i]['cnt_tms'];

            $sum_budget_total_01 = $sum_budget_total_01 + $budget_total_01;
            $sum_budget_total_02 = $sum_budget_total_02 + $budget_total_02;

            $index++;
        }

        $alpha = 'A';
        $sheet->setCellValue($alpha++.$index, "Total 总计");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, "");

        $sheet->setCellValue($alpha++.$index, $sum_rd);
        $sheet->setCellValue($alpha++.$index, $sum_asm);
        $sheet->setCellValue($alpha++.$index, $sum_pcm_leader);
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, $sum_budget_total_01);

        $sheet->setCellValue($alpha++.$index, $sum_assist);
        $sheet->setCellValue($alpha++.$index, $sum_sale);
        $sheet->setCellValue($alpha++.$index, $sum_pcm);
        $sheet->setCellValue($alpha++.$index, $sum_tms);
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, $sum_budget_total_02);

        $index_flag = $index - 1;

        $sheet->setCellValue($alpha++.$index, "=SUM(P3:P".$index_flag.")");
        $sheet->setCellValue($alpha++.$index, "=SUM(Q3:Q".$index_flag.")");
        $sheet->setCellValue($alpha++.$index, "=SUM(R3:R".$index_flag.")");

        $sheet->mergeCells("A".$index.":D".$index);
        $sheet->getStyle("A".$index.":D".$index)->applyFromArray($style);
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'Monthly_Headcount_List_BKK_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

   // Export Store Target
    private function _exportStoreTargetBySale($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            // 'Grand Area',
            'Area',
            'Province',
            'Sale Code',
            'Sale Name',
            // 'OPPO ID',
            'Store Code',
            'Store Name',
            'PCDB Code',
            'PCDB Name',
            'Number of PC',
            'PC Code',
            'PC Name',
            'Target',
            'Hero Target',
            'Target Sellout',
            'Target Activated',
            'Hero Sellout',
            'Hero Activated',
            'Achieve Hero Sellout',
            'Achieve Hero Activated',
            // 'Price Sellout',
            'Achieve Target Sellout',
            'Achieve Target Activate',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();
        $QStoreStaff = new Application_Model_StoreStaff();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
        $no = 1;
        for ($i=0;$i<count($data); $i++) {

            $achieve_price = round(( $data[$i]['sellout'] / $data[$i]['st_target_price'] ) * 100, 2);
            $achieve_saleout_activate = round(( $data[$i]['activate'] / $data[$i]['sellout'] ) * 100, 2);
            $achieve_hero_sellout = round(( $data[$i]['hero_product_sellout'] / $data[$i]['target_hero'] ) * 100, 2);
            $achieve_hero_activate = round(( $data[$i]['hero_product_activate'] / $data[$i]['hero_product_sellout'] ) * 100, 2);

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $no);
            // $sheet->setCellValue($alpha.$index, $grand_area);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['grand_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['province']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_name']);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['oppo_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);

            $pcdb_list = $QStoreStaff->getPCDB($data[$i]['st_id'], 0);
            $cnt = count($pcdb_list);
            $pcdb_name = "";
            $pcdb_code = "";

            if ($cnt > 0 ) {
                for ($j=0;$j<$cnt;$j++) {
                    if ($j != $cnt-1) { $pcdb_name = $pcdb_name.$pcdb_list[$j]['pcdb_name']." / "; }
                    else { $pcdb_name = $pcdb_name.$pcdb_list[$j]['pcdb_name']; }
                }
            } else { $pcdb_name = "-"; }

            if ($cnt > 0 ) {
                for ($j=0;$j<$cnt;$j++) {
                    if ($j != $cnt-1) { $pcdb_code = $pcdb_code.$pcdb_list[$j]['cp_code']." / "; }
                    else { $pcdb_code = $pcdb_code.$pcdb_list[$j]['cp_code']; }
                }
            } else { $pcdb_code = "-"; }

            $sheet->setCellValue($alpha++.$index, $pcdb_code);
            $sheet->setCellValue($alpha++.$index, $pcdb_name);

            //Get Number Of Pc And code
            $staff_list = $QStoreStaff->getPC($data[$i]['st_id'], 0);
            $cnt = count($staff_list);
            $pc_code = "";
            if ($cnt > 0 ) {
                for ($j=0;$j<$cnt;$j++) {
                    if ($j != $cnt-1) { $pc_code = $pc_code.$staff_list[$j]['pc_code']." / "; }
                    else { $pc_code = $pc_code.$staff_list[$j]['pc_code']; }
                }
            } else { $pc_code = "-"; }

            $sheet->setCellValue($alpha++.$index,$cnt);
            $sheet->setCellValue($alpha++.$index,$pc_code);
            
            $staff_list = $QStoreStaff->getPC($data[$i]['st_id'], 0);
            $cnt = count($staff_list);
            $pc_name = "";

            if ($cnt > 0 ) {
                for ($j=0;$j<$cnt;$j++) {
                    if ($j != $cnt-1) { $pc_name = $pc_name.$staff_list[$j]['pc_name']." / "; }
                    else { $pc_name = $pc_name.$staff_list[$j]['pc_name']; }
                }
            } else { $pc_name = "-"; }

            $sheet->setCellValue($alpha++.$index,$pc_name);

            $sheet->setCellValue($alpha++.$index, $data[$i]['st_target_price']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['target_hero']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['activate']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_product_sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_product_activate']);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_price']);
            $sheet->setCellValue($alpha++.$index, number_format($achieve_hero_sellout,2)." %");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_hero_activate,2)." %");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_price,2)." %");
            $sheet->setCellValue($alpha++.$index, number_format($achieve_saleout_activate,2)." %");

            $index++;
            $no++;
        }
        
        $filename = 'Export_Store_Target_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Store Price Target : Export Store Price OverViewTarget
    private function _exportStorePriceOverViewTarget($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'Grand Area',
            'Area',
            'RD',
            'Area Price Target',
            'Leader Code',
            'Leader Name',
            'Group',
            'Sale Price Target',
            'Store ID',
            'Store Name',
            'Store Type',
            'Store Status',
            'Market Type',
            'Market Name',
            'Number of PC',
            'Number of PC Stand By',
            'Store Price Target',
            'Store Unit Sellout',
            'Store Price Sellout',
            'Store Achieve',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QAsm = new Application_Model_Asm();
        $QSPT = new Application_Model_StorePriceTarget();
        $QStoreStaff = new Application_Model_StoreStaff();

        // Get Market Details
        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        // Prepare Area Target 
        $temp_area = $result_by_area = array();
        $temp_area = $QSPT->checkTargetByArea($params);
        foreach ($temp_area as $key => $value) {
            $result_by_area[ $value['area_id'] ] = $value['target_price'];
        }

        // Prepare Sale Target 
        foreach ($data as $key => $value) {
            $result_by_sale[ $value['area_id']."|".$value['staff_code'] ] += $value['target_price'];
        }

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            // Get RD Details
            $rd_name = '';
            $result_asm = $QAsm->getStaffByAreaID($data[$i]['area_id'], 1);
            if ( !empty($result_asm) ) {
                if ( count($result_asm) > 1 ) {
                    foreach ($result_asm as $key => $value) { $rd_name .= $value['staff_name']." | "; }
                    $rd_name = substr($rd_name, 0, -3);
                } else {
                    $rd_name = $result_asm[0]['staff_name'];
                }
            }

            // Get PC Details
            $result_pc_01 = $QStoreStaff->getPC($data[$i]['st_id'], 0); // PC
            $result_pc_02 = $QStoreStaff->getPC($data[$i]['st_id'], 1); // PC Stand By 
            $cnt_pc_01 = count($result_pc_01);
            $cnt_pc_02 = count($result_pc_02);

            // Get Store Sellout 
            $sellout_unit = $sellout_price = 0;
            $store_sellout = $QSPT->getSelloutByStore($params, $data[$i]['st_id']);
            if ( !empty($store_sellout) ) {
                $sellout_unit = $store_sellout['sellout_unit'];
                $sellout_price = $store_sellout['sellout_price'];
            }

            // Calcualte Store Achieve 
            $achieve = round(( $sellout_price / $data[$i]['target_price'] ) * 100, 2);

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $data[$i]['grand_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);

            $sheet->setCellValue($alpha++.$index, $rd_name);
            $sheet->setCellValue($alpha++.$index, $result_by_area[ $data[$i]['area_id'] ]);

            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++.$index, $result_by_sale[ $data[$i]['area_id']."|".$data[$i]['staff_code'] ]);

            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_status']);

            $sheet->setCellValue($alpha++.$index, $store_market[ $data[$i]['st_id'] ]['market_type']);
            $sheet->setCellValue($alpha++.$index, $store_market[ $data[$i]['st_id'] ]['market_name']);

            $sheet->setCellValue($alpha++.$index, $cnt_pc_01);
            $sheet->setCellValue($alpha++.$index, $cnt_pc_02);

            $sheet->setCellValue($alpha++.$index, $data[$i]['target_price']);

            $sheet->setCellValue($alpha++.$index, $sellout_unit);
            $sheet->setCellValue($alpha++.$index, $sellout_price);
            $sheet->setCellValue($alpha++.$index, number_format($achieve,2)." %");

            $index++;
        }

        $filename = 'StorePriceOverViewTarget_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Store Price Target : Export Sale Com Target
    private function _exportSaleComTarget($data,$params) {

        // Get Total Day of Month
        $total_days = date('t', strtotime($params['from']));

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'Area',
            'Store ID',
            'Store Name',
            'Store Type',
            'Store Status',
            'Store Price Target',
            'Sale Code',
            'Sale Name',
            'Current Group',
            'Off Date',
            'Start Date',
            'End Date',
            'Total Days',
            'Sale Price Target',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QSPT = new Application_Model_StorePriceTarget();

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            // Calculate Total Days
            if ( $data[$i]['st_joined_at'] <= $params['from']." 00:00:00" ) { $start = $params['from']." 00:00:00"; } 
            else { $start = $data[$i]['st_joined_at']; }

            if ( !isset($data[$i]['st_released_at']) || $data[$i]['st_released_at'] >= $params['to']." 23:59:59" ) { $end = $params['to']." 23:59:59"; $date_flag = 1; } 
            else { $end = $data[$i]['st_released_at']; $date_flag = 0; }

            $diffdate = strtotime($end) - strtotime($start);
            $days = floor($diffdate/(60*60*24)) + $date_flag;

            // Calculate Sale Target 
            $sale_target = round( ($data[$i]['st_target_price'] * $days) / $total_days, 0);

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_status']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_target_price']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['off_date']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_joined_at']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_released_at']);
            $sheet->setCellValue($alpha++.$index, $days);
            $sheet->setCellValue($alpha++.$index, $sale_target);

            $index++;
        }

        $filename = 'SaleComTarget_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Monthly KPI Headcount List
    public function _exportKpiHeadcountList($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area Name',
            'Assistant',
            'PCM Leader',
            'PCM',
            'TMS',
            'Sale',
            'Total',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle('A1:H1')->applyFromArray($style);

        $index = 2;

        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_assist']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm_leader']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_tms']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_total']);

            $index++;
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'Monthly_KPI_Headcount_List_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Monthly Headcount Detials
    public function _exportKpiHeadcountDetails($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area Name',
            'Staff Code',
            'Staff Name',
            'Nick Name',
            'Staff Group',
            'Joined At',
            'Off Date',
            'Phone Number',
            'RD Code',
            'RD Name',
            'Month-Year',
            'Remark',
            'Created By [Code]',
            'Created By [Name]',
            'Created By [Group]',
            'Created At',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle('A1:Q1')->applyFromArray($style);

        $QStaff = new Application_Model_Staff();

        $index = 2;

        for ($i=0;$i<count($data); $i++) {

            $where = $QStaff->getAdapter()->quoteInto('code = ?', $data[$i]['staff_code']);
            $result_staff = $QStaff->fetchRow($where);

            $staff_off_date = $staff_joined_at = '';
            if ( !empty($result_staff) ) { 
                $staff_off_date = $result_staff['off_date']; 
                $staff_joined_at = $result_staff['joined_at']; 
            } 

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_nickname']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++.$index, $staff_joined_at);
            $sheet->setCellValue($alpha++.$index, $staff_off_date);
            $sheet->setCellValue($alpha++.$index, '="'.$data[$i]['phone_number'].'"');
            $sheet->setCellValue($alpha++.$index, $data[$i]['rd_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['rd_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['month_year']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['remark']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['created_by_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['created_by_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['created_by_group']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['created_at']);

            $index++;
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'Monthly_KPI_Headcount_Details_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Monthly Birthday PC List
    public function _exportBirthdayPcList($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area Name',
            'RD',
            'ASM',
            'Sale',
            'PC',
            'Assistant',
            'Admin',
            'PCM Leader',
            'PCM',
            'TMS',
            'BM',
            'Total',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle('A1:J1')->applyFromArray($style);

        $index = 2;

        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_rd']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_asm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pc']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_assist']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_admin']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm_leader']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_tms']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_bm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_total']);

            $index++;
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'Monthly_Birthday_Staff_List_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Monthly Birthday PC Detials
    public function _exportBirthdayPcDetails($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area Name',
            'Staff Code',
            'Staff Name',
            'Staff Group',
            'Off Date',
            'Phone Number',
            'Birthday Gift',
            'ASM Name',
            'RD Name',
            'Month-Year',
            'Created By [Code]',
            'Created By [Name]',
            'Created By [Group]',
            'Created At',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle('A1:J1')->applyFromArray($style);

        $QStaff = new Application_Model_Staff();

        $index = 2;
        $birthday_gift = 200;

        for ($i=0;$i<count($data); $i++) {

            $where = $QStaff->getAdapter()->quoteInto('code = ?', $data[$i]['staff_code']);
            $result_staff = $QStaff->fetchRow($where);

            $staff_off_date = '';
            if ( !empty($result_staff) ) { $staff_off_date = $result_staff['off_date']; } 

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++.$index, $staff_off_date);
            $sheet->setCellValue($alpha++.$index, '="'.$data[$i]['phone_number'].'"');
            $sheet->setCellValue($alpha++.$index, $birthday_gift);
            $sheet->setCellValue($alpha++.$index, $data[$i]['asm_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['rd_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['month_year']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['created_by_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['created_by_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['created_by_group']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['created_at']);

            $index++;
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'Monthly_Birthday_Staff_Details_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Monthly Birthday List By Area 
    public function _exportBirthdayPcListByArea($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $heads_01 = array("สรุปค่าใช้จ่ายประจำเดือน ".$params['month_year']." [By Area]");

        $alpha = 'A';
        $index = 1;
        foreach ($heads_01 as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $heads_02 = array(
            'No. 序号',
            'Area 区域',
            'ชี่อ-นามสกุล ที่จะโอนเงิน 需要转账的名单',
            'ตำแหน่ง 职位',
            'RD',
            'ASM',
            'Sale',
            'PC',
            'Assistant',
            'Admin',
            'PCM Leader',
            'PCM',
            'TMS',
            'BM',
            'Total 总计',
            'งบค่าใช้จ่าย / คน 预算费/人',
            'สรุปยอดค่าใช้จ่าย 消费总计',
        );

        $alpha    = 'A';
        $index    = 2;
        foreach($heads_02 as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:G1');
        $sheet->getStyle("A1:G2")->applyFromArray($style);

        // $QMBP = new Application_Model_MonthlyBirthdayPc();
        $QAsm = new Application_Model_Asm();

        $birthday_gift = 200;
        $budget_total = 0;
        $index = 3;

        $sum_rd = $sum_assist = $sum_asm = $sum_pcm_leader = $sum_sale = $sum_pcm = $sum_tms = $sum_total = 0;
        $sum_budget_total = 0;

        for ($i=0;$i<count($data); $i++) {

            $leader_name = $leader_group = '';

            $budget_total = ($data[$i]['cnt_rd'] + $data[$i]['cnt_asm'] + $data[$i]['cnt_sale'] + $data[$i]['cnt_pc'] + $data[$i]['cnt_assist'] + $data[$i]['cnt_admin'] + $data[$i]['cnt_pcm_leader'] + $data[$i]['cnt_pcm'] + $data[$i]['cnt_tms'] + $data[$i]['cnt_bm']) * $birthday_gift;
/*
            $params2 = array(
                'area_id'    => $data[$i]['area_id'], 
                'month_year' => $params['month_year'],
                'flag'       => 1,
            );

            $result_leader = $QMBP->getMonthlyBirthdayPcDetails($params2);
            // print_r($result_leader); die;

            if ( !empty($result_leader) ) {

                if ( $result_leader[0]['asm_name'] <> '' ) { 
                    $leader_name = $result_leader[0]['asm_name']; 
                    $leader_group = 'ASM / ASM Stand By';
                } else {
                    $leader_name = $result_leader[0]['rd_name']; 
                    $leader_group = 'RD';
                }

            } 
*/

            $result_rd = $QAsm->getStaffByAreaID($data[$i]['area_id'], 1);

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);

            $sheet->setCellValue($alpha++.$index, $result_rd[0]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $result_rd[0]['group_name']);

            // $sheet->setCellValue($alpha++.$index, $leader_name);
            // $sheet->setCellValue($alpha++.$index, $leader_group);

            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_rd']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_asm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pc']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_assist']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_admin']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm_leader']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_tms']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_bm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_total']);

            $sheet->setCellValue($alpha++.$index, $birthday_gift);
            $sheet->setCellValue($alpha++.$index, $budget_total);

            $sum_rd         = $sum_rd           + $data[$i]['cnt_rd'];
            $sum_asm        = $sum_asm          + $data[$i]['cnt_asm'];
            $sum_sale       = $sum_sale         + $data[$i]['cnt_sale'];
            $sum_pc         = $sum_pc           + $data[$i]['cnt_pc'];
            $sum_assist     = $sum_assist       + $data[$i]['cnt_assist'];
            $sum_admin      = $sum_admin        + $data[$i]['cnt_admin'];
            $sum_pcm_leader = $sum_pcm_leader   + $data[$i]['cnt_pcm_leader'];
            $sum_pcm        = $sum_pcm          + $data[$i]['cnt_pcm'];
            $sum_tms        = $sum_tms          + $data[$i]['cnt_tms'];
            $sum_bm         = $sum_bm           + $data[$i]['cnt_bm'];
            $sum_total      = $sum_total        + $data[$i]['cnt_total'];

            $sum_budget_total = $sum_budget_total + $budget_total;

            $index++;
        }

        $alpha = 'A';
        $sheet->setCellValue($alpha++.$index, "Total 总计");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, "");

        $sheet->setCellValue($alpha++.$index, $sum_rd);
        $sheet->setCellValue($alpha++.$index, $sum_asm);
        $sheet->setCellValue($alpha++.$index, $sum_sale);
        $sheet->setCellValue($alpha++.$index, $sum_pc);
        $sheet->setCellValue($alpha++.$index, $sum_assist);
        $sheet->setCellValue($alpha++.$index, $sum_admin);
        $sheet->setCellValue($alpha++.$index, $sum_pcm_leader);
        $sheet->setCellValue($alpha++.$index, $sum_pcm);
        $sheet->setCellValue($alpha++.$index, $sum_tms);
        $sheet->setCellValue($alpha++.$index, $sum_bm);
        $sheet->setCellValue($alpha++.$index, $sum_total);
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, $sum_budget_total);

        $sheet->mergeCells("A".$index.":D".$index);
        $sheet->getStyle("A".$index.":D".$index)->applyFromArray($style);
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'Monthly_Birthday_Staff_List_By_Area'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export PC Un-Punish List
    private function _exportPcUnpunishList($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Month-Year',
            'Area Name',
            'Staff Code',
            'Staff Name',
            'Position',
            'Un-Punish Type',
            '200k',
            'Index',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle('A1:H1')->applyFromArray($style);

        $index = 2;

        for ($i=0;$i<count($data); $i++) {

            $month_year = date('M Y', strtotime($data[$i]['from_date']));

            $con_01 = $con_02 = "No";
            
            if ( $data[$i]['con_01'] == 1 ) { $con_01 = "Yes"; }
            if ( $data[$i]['con_02'] == 1 ) { $con_02 = "Yes"; }

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $month_year);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['unpunish_type']);
            $sheet->setCellValue($alpha++.$index, $con_01);
            $sheet->setCellValue($alpha++.$index, $con_02);

            $sum_price = $sum_price + $data[$i]['price'];

            $index++;
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        $filename = 'PC_UnPunish_List_'.date('Y-m-d');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

}