install-hooks:
	@echo "🔧 Instalando pre-commit hook..."
	@mkdir -p .git/hooks
	@cp scripts/git-hooks/pre-commit .git/hooks/pre-commit
	@chmod +x .git/hooks/pre-commit
	@echo "✅ Hook pre-commit instalado correctamente."
