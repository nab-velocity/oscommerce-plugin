Velocity OsCommerce Module Installation Documentation 

1.	Download velocity OsCommerce Module by clicking on Download zip button on the right bottom of this page.

2.	Configuration Requirement: OsCommerce site Version 2.3 or above version must be required for our velocity payment module installation.

3.	Installation & Configuration of Module from Admin Panel:
	  Unzip module code and upload on your server as per the directory structure matched with your OsCommerce dircetory structure and admin folder may be change but admin folder content upload in your admin folder after upload the code on server login admin panel and click on 'Modules' Menu option then click on 'payment'.

Show the list of all installed payment module listed for new installation click on top right button "Install Module" after succesfull upload from server side your velocity module is also listed.

Select our module after that right side of panel show a button for install the module click on that button after installation module listed in installed list of modules and configure the module save velocity credential and also enbale/disable module on Testing mode or production mode.

VELOCITY CREDENTIAL DETAILS
1.	Identity Token: - This is security token provided by velocity to merchant.
2.	WorkFlowId/ServiceId: - This is servuce id provided by velocity to merchant.
3.	ApplicationProfileId: - This is Application id provided by velocity to merchant.
4.	MerchantProfileId: - This is Merchant id provided by velocity to merchant.
5.	Test Mode :- This is for test the module, if select dropdwon for test mode enable and no need to save “WorkFlowId/ServiceId & MerchantProfileId” otherwise select the production mode and save “WorkFlowId/ServiceId & MerchantProfileId” for live payment.

For update/uninstall the velocity module of OsCommerce goto Modules Menu option then select module and edit for change the configuration and for uninstallation remove the module but code not remove from sever.

4.  We have saved the raw request and response objects in &lt;prefix&gt;_velocity_transactions table.