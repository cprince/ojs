package org.pkp.ojs;

import java.io.PrintStream;

public class DoInstall extends OJSTestCase {

	public DoInstall(String name) {
		super(name);
	}

	public void testInstall() throws Exception {
		if (assumeProperty("disableInstall", "Set this property to true to disable installation of OJS.").equals("true")) return;

		String filesDir = assumeProperty("filesDir", "Set this property to the files dir of the OJS install.");
		String databaseDriver = assumeProperty("databaseDriver", "Set this property to the PHP database driver name (e.g. mysql).");

		log("Going to install page... ");
		beginAt("/");
		usualTests();
		assertLinkPresentWithText("OJS Installation");
		setWorkingForm("install");
		setFormElement("locale", "en_US");
		setFormElement("clientCharset", "utf-8");
		setFormElement("connectionCharset", "");
		setFormElement("databaseCharset", "");
		setFormElement("filesDir", filesDir);
		setFormElement("encryption", "sha1");
		setFormElement("adminUsername", this.adminLogin);
		setFormElement("adminPassword", this.adminPassword);
		setFormElement("adminPassword2", this.adminPassword);
		setFormElement("adminEmail", this.adminEmail);
		setFormElement("databaseDriver", databaseDriver);
		uncheckCheckbox("createDatabase");
		setFormElement("databaseHost", "localhost");
		setFormElement("databaseUsername", "ojs2");
		setFormElement("databasePassword", "ojs2");
		setFormElement("databaseName", "ojs2-junit");
		setFormElement("oaiRepositoryId", "junit.ojs.localhost");
		log("Done.\nSubmitting install form... ");
		submit();
		log("Done.\nTesting result...");
		dumpResponse(new PrintStream(System.out));
		assertTextPresent("Installation of OJS has completed successfully.");
		usualTests();
		log("Done.\n");
	}
}