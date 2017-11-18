DOXYGEN := doxygen

doxyfile := Doxyfile

##! Directory containing configuration for and builds of the developer documentation
doc_dir := doc

.PHONY: all
all: doc

.PHONY: doc
##! Generate documentation
##!
##! The documentation is built from inline doxygen directives
doc:
	@(cd $(doc_dir) ; $(DOXYGEN) $(doxyfile))

