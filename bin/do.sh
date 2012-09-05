#!/bin/bash
/usr/local/zend/bin/php \
bin/doctrine2xsd.php doctrine2xsd:generate-xsd modagenzia.xsd 'http://www.goetas.com/immobinet/modagenzia' \
--ns-map='mercurio.ImmobiNetBundle.Entity:/www/htdocs/immobinet-asmir/vendor-dev/bundles/core/mercurio/ImmobiNetBundle/Resources/config/doctrine/:http://www.goetas.com/immobinet' \
--ns-map='mercurio.ModAgenziaBundle.Entity:/www/htdocs/immobinet-asmir/vendor-dev/bundles/mod-agenzia/mercurio/ModAgenziaBundle/Resources/config/doctrine/:http://www.goetas.com/immobinet/modagenzia' \
--allow-map='/www/htdocs/immobinet-asmir/vendor-dev/bundles/mod-agenzia/mercurio/ModAgenziaBundle/Resources/config/doctrine2xsd-allow.xml' \

#!/bin/bash
/usr/local/zend/bin/php \
bin/doctrine2xsd.php doctrine2xsd:generate-xsd immobinet.xsd 'http://www.goetas.com/immobinet' \
--ns-map='mercurio.ImmobiNetBundle.Entity:/www/htdocs/immobinet-asmir/vendor-dev/bundles/core/mercurio/ImmobiNetBundle/Resources/config/doctrine/:http://www.goetas.com/immobinet' \

