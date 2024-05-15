

// Function to fetch and load catalogue options
async function loadCatalogues() {
   try {
     const response = await fetch('https://raw.githubusercontent.com/DELM-net/ms-lookup/main/metadata/ms-catalogues.csv');
     const data = await response.text();
     const lines = data.split('\n');
     const select = document.getElementById('catalogueSelect');
     lines.forEach(line => {
       const [code, shortTitle, longTitle] = parseCSVLine(line);
       const option = document.createElement('option');
       option.value = code;
       option.textContent = shortTitle;
       select.appendChild(option);
     });
   } catch (error) {
     console.error('Error loading catalogues:', error);
   }
 }
 
 // Function to load the resources based on user input
async function loadResource() {
   try {
     const catalogueCode = document.getElementById('catalogueSelect').value;
     const resourceName = document.getElementById('textInput').value;
     const resourceLocation = `https://raw.githubusercontent.com/DELM-net/ms-lookup/main/data/${catalogueCode}/${resourceName}.csv`;
 
     const response = await fetch(resourceLocation);
     if (!response.ok) {
       throw new Error('Resource not found.');
     }
 
     const resourceData = await response.text();
     console.log('Resource data:', resourceData); // Log resource data
     document.getElementById('resourceTable').innerHTML = resourceData; // Directly display resource data
   } catch (error) {
     console.error('Error loading resource:', error);
     alert(error.message);
   }
 }
   
 // Function to parse CSV line and handle quotes
 function parseCSVLine(line) {
   const values = [];
   let current = '';
   let insideQuotes = false;
   for (let i = 0; i < line.length; i++) {
     if (line[i] === '"') {
       insideQuotes = !insideQuotes;
     } else if (line[i] === ',' && !insideQuotes) {
       values.push(current.trim());
       current = '';
     } else {
       current += line[i];
     }
   }
   values.push(current.trim());
   return values;
 }
 
 // Function to parse and display resources in the table
function displayResources(data) {
   console.log('Resource data:', data);
   const rows = data.split('\n');
   const tableBody = document.querySelector('#resourceTable tbody');
   tableBody.innerHTML = ''; // Clear previous rows
 
   rows.forEach(row => {
     console.log('Row:', row);
     const [fileName, description, link] = parseCSVLine(row);
     console.log('Parsed values:', fileName, description, link);
     const escapedDescription = description.replace(/\"/g, ''); // Remove quotes from description
     const escapedLink = link.replace(/\"/g, ''); // Remove quotes from link
     const newRow = `<tr><td>${escapedDescription}</td><td><a href="${escapedLink}" target="_blank">${escapedLink}</a></td></tr>`;
     tableBody.insertAdjacentHTML('beforeend', newRow);
   });
 }
 
 
 // Load catalogues when the page loads
 window.onload = loadCatalogues;
 