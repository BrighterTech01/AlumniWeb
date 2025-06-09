<div class="overflow-x-auto mt-6">
    <table class="min-w-full divide-y divide-lighter/10">
        <thead class="bg-dark/50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Course</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Batch</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Graduation Year</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-dark divide-y divide-lighter/10">
            <?php
            $query = "SELECT a.*, c.name as course_name FROM alumnus a 
                      LEFT JOIN courses c ON a.course_id = c.course_id 
                      ORDER BY a.lastname ASC";
            $result = mysqli_query($conn, $query);
            
            while($alumni = mysqli_fetch_assoc($result)) {
                $statusClass = $alumni['status'] == 1 ? 'text-green-400' : 'text-red-400';
                $statusText = $alumni['status'] == 1 ? 'Active' : 'Inactive';
                
                echo '
                <tr class="hover:bg-light/5 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full" src="../assets/uploads/alumni/'.$alumni['image'].'" alt="">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-white">'.$alumni['firstname'].' '.$alumni['lastname'].'</div>
                                <div class="text-sm text-gray-400">'.$alumni['email'].'</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">'.$alumni['course_name'].'</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">'.$alumni['batch'].'</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">'.$alumni['graduation_year'].'</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full '.$statusClass.'">
                            '.$statusText.'
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="loadViewModal(\''.$alumni['alumnus_id'].'\')" class="p-2 text-gray-400 hover:text-neon-blue">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="loadEditModal(\''.$alumni['alumnus_id'].'\')" class="p-2 text-gray-400 hover:text-neon-purple">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="confirmDelete(\''.$alumni['alumnus_id'].'\', \''.htmlspecialchars($alumni['firstname'].' '.$alumni['lastname']).'\')" class="p-2 text-gray-400 hover:text-red-500">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>';
            }
            ?>
        </tbody>
    </table>
</div>