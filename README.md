# FAA CSV to SQL (FC2S)

This project, FC2S (FAA CSV to SQL), is designed to convert Federal Aviation Administration (FAA) data from Comma Separated Values (CSV) format to Structured Query Language (SQL). The intention of the project is to download the latest aeronautical data published by the FAA on a 28-day cycle, which they publish in CSV format. The process includes downloading the data, unzipping it, creating a new database, creating the tables (based on the current schema provided by the FAA with the download), importing the data, then exporting the data as individual .sql files by table and also one large .sql file with all the tables. The final step is to zip them up and make them available to anyone who needs the data in SQL format under the MIT License.

## Acronyms

- **FAA**: Federal Aviation Administration
- **CSV**: Comma Separated Values
- **SQL**: Structured Query Language
- **FC2S**: FAA CSV to SQL

## Process

1. Download the latest aeronautical data published by the FAA in CSV format.
2. Unzip the downloaded data.
3. Create a new database.
4. Create tables based on the current schema provided by the FAA with the download.
5. Import the data into the newly created tables.
6. Export the data as individual .sql files by table and also one large .sql file with all the tables.
7. Zip the .sql files and make them available under the MIT License.

## Usage

