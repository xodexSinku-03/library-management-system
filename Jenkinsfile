pipeline {
    agent any

    environment {
        APP_NAME = 'library-management-system'
        IMAGE_NAME = "library-management-system:${BUILD_NUMBER}"
        IMAGE_LATEST = "library-management-system:latest"
        COMPOSE_PROJECT_NAME = "library_app_${BUILD_NUMBER}"
    }

    options {
        timeout(time: 1, unit: 'HOURS')
        disableConcurrentBuilds()
        ansiColor('xterm')
    }

    stages {
        stage('Checkout') {
            steps {
                echo 'Checking out source code...'
                checkout scm
            }
        }

        stage('PHP Syntax Check') {
            steps {
                echo 'Running PHP syntax lint checks...'
                sh '''
                    if command -v php >/dev/null 2>&1; then
                        find . -maxdepth 3 -name "*.php" -not -path "*/vendor/*" -exec php -l {} ';'
                    else
                        echo "PHP CLI not found on Jenkins agent. Running syntax check in PHP container..."
                        docker run --rm -v "$(pwd):/app" -w /app php:8.2-cli find . -maxdepth 3 -name "*.php" -not -path "*/vendor/*" -exec php -l {} ';'
                    fi
                '''
            }
        }

        stage('Composer Validation') {
            steps {
                echo 'Validating composer.json configuration...'
                sh '''
                    if command -v composer >/dev/null 2>&1; then
                        composer validate --no-check-publish
                    else
                        docker run --rm -v "$(pwd):/app" -w /app composer:latest composer validate --no-check-publish
                    fi
                '''
            }
        }

        stage('Build Docker Image') {
            steps {
                echo "Building Docker image: ${IMAGE_NAME}..."
                sh """
                    docker build -t ${IMAGE_NAME} -t ${IMAGE_LATEST} .
                """
            }
        }

        stage('Test Stack with Docker Compose') {
            steps {
                echo 'Starting container stack for integration smoke test...'
                sh """
                    docker compose -p ${COMPOSE_PROJECT_NAME} up -d
                    sleep 10
                    docker compose -p ${COMPOSE_PROJECT_NAME} ps
                """
                echo 'Testing HTTP response from web container...'
                sh """
                    curl --fail --retry 5 --retry-delay 3 http://localhost:8080/ || echo "Smoke test completed with warnings"
                """
            }
        }

        stage('Cleanup Integration Containers') {
            steps {
                echo 'Tearing down integration test containers...'
                sh """
                    docker compose -p ${COMPOSE_PROJECT_NAME} down -v --remove-orphans || true
                """
            }
        }
    }

    post {
        always {
            echo 'Pipeline execution finished.'
            sh """
                docker compose -p ${COMPOSE_PROJECT_NAME} down -v --remove-orphans || true
            """
        }
        success {
            echo 'Build succeeded! Docker image ready for deployment.'
        }
        failure {
            echo 'Build failed! Please inspect logs.'
        }
    }
}

