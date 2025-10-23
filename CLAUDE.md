# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

BookLend is a web application for managing book lending operations. The project is hosted in a XAMPP environment, indicating it will likely use PHP for backend operations with Apache as the web server.

## Development Environment

- **Server**: XAMPP (Apache + MySQL/MariaDB + PHP)
- **Location**: `/mnt/c/xampp/htdocs/booklend`
- **Platform**: Linux (WSL2)

## Getting Started

Since this is a new repository, these sections should be updated as the project structure develops:

### Development Commands

```bash
# Start XAMPP services (from Windows if using WSL)
# Navigate to XAMPP control panel and start Apache and MySQL

# Access application
# http://localhost/booklend
```

### Project Structure

*To be documented as the codebase develops. Expected structure:*
- Database schema and migration files
- Backend PHP files (controllers, models, etc.)
- Frontend assets (HTML, CSS, JavaScript)
- Configuration files (database connections, environment settings)

## Architecture Notes

*This section should be updated with:*
- Database schema overview and relationships
- Authentication/authorization approach
- Session management strategy
- API endpoints or routing structure (if applicable)
- Key business logic flows (lending, returning, user management)

## Important Considerations

- Ensure database credentials are stored in configuration files that are not committed to version control
- Add `.gitignore` to exclude sensitive files and temporary data
