# FAA CSV to SQL (FC2S)

This project, FC2S (FAA CSV to SQL), is designed to convert Federal Aviation Administration (FAA) data from Comma Separated Values (CSV) format to Structured Query Language (SQL). The project intends to download the latest aeronautical data published by the FAA on a 28-day cycle, which they publish in CSV format. The process includes downloading the data, unzipping it, creating a new database, creating the tables (based on the current schema provided by the FAA with the download), importing the data, and then exporting the data as one large .sql file with all the tables. The final step is to zip them up and make them available to anyone who wants the data in SQL format.

FMI: https://www.faa.gov/air_traffic/flight_info/aeronav/aero_data/NASR_Subscription/

If you are only looking for the final data in SQL format you can download every available cycle from our repository:

https://github.com/aifrus/nasr_sql_zips

## Acronyms

- **FAA**: Federal Aviation Administration
- **CSV**: Comma Separated Values
- **SQL**: Structured Query Language
- **FC2S**: FAA CSV to SQL

## Process

1. Download the latest aeronautical data published by the FAA in CSV format.
2. Unzip the downloaded data.
3. Create a new database.
4. Create tables based on the current schema the FAA provides with the download.
5. Import the data into the newly created tables.
6. Export the data as one large .sql file with all the tables.
7. Zip the .sql file.

## Notes

You must set `mysqli.allow_local_infile` in `php.ini` to allow `LOAD DATA LOCAL INFILE` operations within PHP scripts.
