# Created by Olav Hardang #

from datetime import datetime, timedelta
import datetime
import mysql.connector
import uuid

# Database connection information
db_config = {
    'host': '',
    'user': '',
    'password': '',
    'database': ''
}

# Connect to the database
conn = mysql.connector.connect(**db_config)
cursor = conn.cursor()

try:
    # Calculate the end time, which is 60 minutes from the current time
    end_time = datetime.datetime.now() + datetime.timedelta(days=2)

    while True:
        # Fetch the latest sendetid from fk_sendeplan
        cursor.execute("SELECT prog_id, duration, sendetid FROM fk_sendeplan ORDER BY sendetid DESC LIMIT 1")

        sendetid_siste = cursor.fetchone()
        #print(sendetid_siste)

        if sendetid_siste:
            # Convert the datetime object back to string with microsecond part padded with zeros
            sendetid_siste_formatted = datetime.datetime.strptime(sendetid_siste[2], '%Y-%m-%d %H:%M:%S.%f')
            #print(sendetid_siste_formatted)

            if sendetid_siste_formatted >= end_time:
                break  # Exit the loop if the latest sendetid is beyond the end_time

            if sendetid_siste:
                last_scheduled_program_id, last_duration, last_sendetid = sendetid_siste
                #print("ENTERING SCHEDULING NEW PROGRAM")
                #print(sendetid_siste)
                #print(last_duration)
                #print(last_sendetid)

                # Get a random item from fk_programbank that is not the same as the last scheduled item
                cursor.execute("SELECT prog_id, filnavn, duration FROM fk_programbank WHERE prog_id != %s AND live = 1 ORDER BY RAND() LIMIT 1", (last_scheduled_program_id,))

                # Fetch the random program
                program = cursor.fetchone()

                if program:
                    prog_id, filnavn, duration = program

                    # Convert last_sendetid to a datetime object if it exists
                    if last_sendetid:
                        last_sendetid = datetime.datetime.strptime(last_sendetid, '%Y-%m-%d %H:%M:%S.%f')
                    else:
                        last_sendetid = datetime.datetime.now()

                    # Parse duration string into hours, minutes, seconds, and milliseconds
                    hours, minutes, seconds_milliseconds = last_duration.split(':')
                    seconds, milliseconds = seconds_milliseconds.split('.')
                    hours, minutes, seconds, milliseconds = map(int, [hours, minutes, seconds, milliseconds])

                    # Create a timedelta object for the duration of the last program
                    last_program_duration = datetime.timedelta(hours=hours, minutes=minutes, seconds=seconds, microseconds=int(milliseconds))

                    #print(last_sendetid)
                    #print(last_program_duration)
                    # Calculate the start time for scheduling the next program
                    start_time = last_sendetid + last_program_duration + datetime.timedelta(seconds=8)
                    start_time = start_time.strftime('%Y-%m-%d %H:%M:%S.%f')
                    #start_time = last_sendetid + last_duration
                    #print("START TIME", start_time)

                    auto = (f"auto_filler")

                    # Insert the program into fk_sendeplan
                    db_id = str(uuid.uuid4())  # Generate a unique ID
                    insert_query = "INSERT INTO fk_sendeplan (prog_id, sendetid, filnavn, duration, db_id, producer) VALUES (%s, %s, %s, %s, %s, %s)"
                    cursor.execute(insert_query, (prog_id, start_time, filnavn, duration, db_id, auto))

                    print("Program scheduled:", start_time, "with duration of", prog_id, duration, filnavn, auto)

                    conn.commit()

                else:
                    print("No live programs found in fk_programbank")
                    # Exit the loop if no live programs found
                    break

            else:
                print("No last scheduled program found in fk_sendeplan")
                # Set default values for last_duration, last_sendetid, etc.
                last_duration = '00:00:01.000000'  # Default duration
                start_time = datetime.datetime.now()  # Default sendetid
                prog_id = 0  # Default prog_id
                filnavn = "NO FILE"  # Default filename
                duration = "00:00:01.000000"  # Default duration
                auto = (f"auto_filler")

                # Insert the program into fk_sendeplan
                db_id = str(uuid.uuid4())  # Generate a unique ID

                insert_query = "INSERT INTO fk_sendeplan (prog_id, sendetid, filnavn, duration, db_id, producer) VALUES (%s, %s, %s, %s, %s, %s)"
                cursor.execute(insert_query, (prog_id, start_time, filnavn, duration, db_id, auto))

                print("Program scheduled:", start_time, "with duration of", prog_id, duration, filnavn, auto)

                conn.commit()

        else:
            print("No last scheduled program found in fk_sendeplan")
            # Set default values for last_duration, last_sendetid, etc.
            last_duration = '00:00:01.000000'  # Default duration
            start_time = datetime.datetime.now()  # Default sendetid
            prog_id = 0  # Default prog_id
            filnavn = "NO FILE"  # Default filename
            duration = "00:00:01.000000"  # Default duration
            auto = (f"auto_filler")

            # Insert the program into fk_sendeplan
            db_id = str(uuid.uuid4())  # Generate a unique ID

            insert_query = "INSERT INTO fk_sendeplan (prog_id, sendetid, filnavn, duration, db_id, producer) VALUES (%s, %s, %s, %s, %s, %s)"
            cursor.execute(insert_query, (prog_id, start_time, filnavn, duration, db_id, auto))

            print("Program scheduled:", start_time, "with duration of", prog_id, duration, filnavn, auto)

            conn.commit()
            print("No sendetid found in fk_sendeplan, but added an initial one!")
            break  # Exit the loop if no sendetid found

except mysql.connector.Error as err:
    print("MySQL error:", err)

finally:
    cursor.close()
    conn.close()

