DOXYGEN := doxygen

doxyfile := Doxyfile

##! Directory containing configuration for and builds of the developer documentation
doc_dir := doc

version := $(shell git describe --always 2> /dev/null)

export PROJECT_NUMBER=$(version)

.PHONY: all
all: doc

.PHONY: info
##! Print some information
info:
	@echo "Version: $(version)"

.PHONY: doc
##! Generate documentation
##!
##! The documentation is built from inline doxygen directives
doc:
	@(cd $(doc_dir) ; $(DOXYGEN) $(doxyfile))

