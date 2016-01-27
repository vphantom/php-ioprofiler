PHPCS = phpcs --standard=PEAR --tab-width=4 --ignore=smarty,tpl_c -n

help:
	@echo "Use one of: test"

test:
	@$(PHPCS) *.php
