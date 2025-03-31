# Created by Olav Hardang #

import mysql.connector
from datetime import datetime, timedelta
import subprocess
import socket
import time
import os
import sys

# Function to restart the script
def restart_script():
    print("No future programs found. Restarting the script in 10 seconds...")
    time.sleep(10)  # 10-second delay before restarting
    python = sys.executable
    os.execl(python, python, *sys.argv)

# Connect to the MySQL database
conn = mysql.connector.connect(
    host='192.168.112.80',
    user='c4frikanalen',
    password='olavH2610!',
    database='c4frikanalen_sendeplan'
)

# Create a cursor object to execute SQL queries
cursor = conn.cursor()

# Specify the fields you want to retrieve in the SELECT query
#fields = ['fk_programbank.prog_tittel', 'fk_sendeplan.sendetid', 'fk_sendeplan.filnavn']  # Add the names of the fields you want to retrieve
fields = ['fk_programbank.prog_tittel', 'fk_sendeplan.sendetid', 'fk_sendeplan.filnavn', 'fk_users.username', 'fk_users.full_name', 'fk_users.kontaktperson', 'fk_users.epost', 'fk_users.telefon', 'fk_users.nettsted']

while True:
    # Get the current datetime
    current_datetime = datetime.now()

    # Advance to the next result set to clear the previous result set
    try:
        cursor.nextset()
    except mysql.connector.errors.InterfaceError:
        pass

    # Construct the SELECT query dynamically using string formatting
    select_query = 'SELECT {} FROM fk_sendeplan ' \
                   'JOIN fk_programbank ON fk_sendeplan.prog_id = fk_programbank.prog_id ' \
                   'JOIN fk_users ON fk_programbank.eier = fk_users.username ' \
                   'WHERE fk_sendeplan.sendetid >= %s ' \
                   'ORDER BY fk_sendeplan.sendetid ASC ' .format(', '.join(fields))

    # Execute the SELECT query
    cursor.execute(select_query, (current_datetime,))

    # Fetch all rows from the result set
    rows = cursor.fetchall()

    # Flag to check if future program is found
    future_program_found = False

    for row in rows:
        #prog_tittel, send_time, filnavn = row
        prog_tittel, send_time, filnavn, username, full_name, kontaktperson, epost, telefon, nettsted = row

        # Convert send_time to datetime object
        send_time = datetime.strptime(send_time, '%Y-%m-%d %H:%M:%S.%f')

        # Calculate the time until the next send time
        time_until_send_time = (send_time - current_datetime).total_seconds()

        if time_until_send_time > 0:
            # Set the flag to indicate a future program is found
            future_program_found = True

            # Print the time to wait
            print(f"Waiting for {timedelta(seconds=time_until_send_time)} until the program '{prog_tittel}' with a start time at '{send_time}'")

            # Sleep until the next send time
            time.sleep(time_until_send_time)

            # Get the command to be executed
            amcp_command_play = f'PLAY 1-3 "{filnavn}"\r\n'
            amcp_command_loadbg = 'LOADBG 1-3 EMPTY CUT AUTO\r\n'
            amcp_command_template = f'CG 1-4 ADD 0 "fk_videorights" 1 "{{\\"f0\\":\\"{kontaktperson} {epost} {telefon} {nettsted}\\", \\"f1\\":\\"{prog_tittel}\\"}}"\r\n'

            try:
                # Open a connection to CasparCG and send the PLAY command
                with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
                    s.connect(('192.168.112.32', 5250))
                    s.sendall(amcp_command_play.encode())

                # Print a message indicating the PLAY command has been executed
                print(f"PLAY command sent for program '{prog_tittel}', filnavn: '{filnavn}'")

                # Wait for 5 seconds
                time.sleep(2)

                # Send the LOADBG command
                with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
                    s.connect(('192.168.112.32', 5250))
                    s.sendall(amcp_command_loadbg.encode())

                # Print a message indicating the LOADBG command has been executed
                print(f"LOADBG command sent for program '{prog_tittel}'")

                # Wait for 5 seconds
                time.sleep(5)
                
                # Send the AMCP command template
                with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
                    s.connect(('192.168.112.32', 5250))
                    s.sendall(amcp_command_template.encode())

                # Print a message indicating the AMCP command template has been sent
                print(f"AMCP command template sent for program '{prog_tittel}'")
            except Exception as e:
                # Print any error that occurred during the socket operation
                print(f"Error sending AMCP commands for program '{prog_tittel}': {e}")

            # Break the loop once the future program is found
            break

    if not future_program_found:
        # If no future programs found, remove the lock file and restart the script
        restart_script()

# Close the cursor and connection
cursor.close()
conn.close()

