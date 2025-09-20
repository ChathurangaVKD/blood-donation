#!/bin/bash
# Blood Donation System - Universal Launcher for Linux/Mac
# Choose your preferred deployment method

show_menu() {
    clear
    echo ""
    echo "🩸 Blood Donation System - Deployment Options"
    echo "============================================="
    echo ""
    echo "Choose your preferred setup method:"
    echo ""
    echo "1. Docker (Recommended - Isolated environment)"
    echo "2. XAMPP (Easy for beginners)"
    echo "3. Native PHP + MySQL (Advanced users)"
    echo "4. Development Server (Quick testing)"
    echo "5. View Documentation"
    echo "6. Exit"
    echo ""
}

while true; do
    show_menu
    read -p "Enter your choice (1-6): " choice

    case $choice in
        1)
            echo ""
            echo "🐳 Starting Docker deployment..."
            if [ -f "./start.sh" ]; then
                chmod +x ./start.sh
                ./start.sh
            else
                echo "❌ Docker setup not found. Please ensure start.sh exists."
                read -p "Press Enter to continue..."
            fi
            ;;
        2)
            echo ""
            echo "🔶 Starting XAMPP setup..."
            if [ -f "./setup_xampp.sh" ]; then
                chmod +x ./setup_xampp.sh
                ./setup_xampp.sh
            else
                echo "❌ XAMPP setup script not found."
                read -p "Press Enter to continue..."
            fi
            ;;
        3)
            echo ""
            echo "🔧 Starting native PHP + MySQL setup..."
            if [ -f "./setup_native.sh" ]; then
                chmod +x ./setup_native.sh
                ./setup_native.sh
            else
                echo "❌ Native setup script not found."
                read -p "Press Enter to continue..."
            fi
            ;;
        4)
            echo ""
            echo "🚀 Starting development server..."
            if [ -f "./start_dev_servers.sh" ]; then
                chmod +x ./start_dev_servers.sh
                ./start_dev_servers.sh
            else
                echo "❌ Development server script not found."
                read -p "Press Enter to continue..."
            fi
            ;;
        5)
            echo ""
            echo "📖 Opening documentation..."
            if [ -f "./NATIVE_SETUP.md" ]; then
                if command -v less &> /dev/null; then
                    less ./NATIVE_SETUP.md
                elif command -v more &> /dev/null; then
                    more ./NATIVE_SETUP.md
                else
                    cat ./NATIVE_SETUP.md
                    read -p "Press Enter to continue..."
                fi
            else
                echo "❌ Documentation not found."
                read -p "Press Enter to continue..."
            fi
            ;;
        6)
            echo ""
            echo "👋 Goodbye!"
            exit 0
            ;;
        *)
            echo ""
            echo "❌ Invalid choice. Please select 1-6."
            read -p "Press Enter to continue..."
            ;;
    esac
done
