CREATE DATABASE IF NOT EXISTS lis_gateway CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lis_gateway;

CREATE TABLE labs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(255) NOT NULL UNIQUE,
    ahfoz      VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id            VARCHAR(50)  PRIMARY KEY,
    name          VARCHAR(255) NOT NULL UNIQUE,
    role          ENUM('Administrator', 'Lab Scientist', 'LIS Manager') NOT NULL,
    lab_name      VARCHAR(255) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE records (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    accession_id         VARCHAR(50)  NOT NULL UNIQUE,
    patient_name         VARCHAR(255) NOT NULL,
    dob                  DATE         NOT NULL,
    test_type            VARCHAR(255) NOT NULL,
    lab_name             VARCHAR(255) NOT NULL,
    status               VARCHAR(50)  NOT NULL DEFAULT 'Pending Review',
    date_time            DATETIME     NOT NULL,
    ordering_physician   VARCHAR(255) DEFAULT NULL,
    submitted_by         VARCHAR(255) DEFAULT NULL,
    scientist_notes      TEXT         DEFAULT NULL,
    authorized_scientist VARCHAR(255) DEFAULT NULL,
    authorized_time      VARCHAR(50)  DEFAULT NULL,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE record_parameters (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    record_id       INT          NOT NULL,
    name            VARCHAR(255) NOT NULL,
    result          VARCHAR(100) NOT NULL,
    reference_range VARCHAR(100) NOT NULL,
    flag            VARCHAR(50)  NOT NULL DEFAULT 'Normal',
    FOREIGN KEY (record_id) REFERENCES records(id) ON DELETE CASCADE
);
