doctrine2xsd
==========

Convert Doctrine2 XML mapping into XSD XML Schema 

Usage
--------------------

```bash

doctrine2xsd.php doctrine2xsd:generate-xsd /tmp/destination.xsd 'http://www.goetas.com/targetXML-Namespace' \
--ns-map='mercurio.ImmobiNetBundle.Entity:/www/htdocs/immobinet-asmir/vendor-dev/bundles/core/mercurio/ImmobiNetBundle/Resources/config/doctrine/:http://www.goetas.com/targetXML-Namespace \

```

This command will pick all Doctrine2 Mapping in `/www/htdocs/immobinet-asmir/vendor-dev/bundles/core/mercurio/ImmobiNetBundle/Resources/config/doctrine/` and create XSD schema `/tmp/destination.xsd`.
Entityes with `mercurio\ImmobiNetBundle\Entity\*`  will be converted in XSD complexType (s) with `http://www.goetas.com/targetXML-Namespace` namespace
